param()

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$marker = 'PIMS-TEST-' + [guid]::NewGuid().ToString('N').Substring(0, 12)
$disposalCode = 'XH-' + $marker
$importCode = 'PN-' + $marker
$importBatchCode = 'LOT-' + $marker
$started = @()
$prescriptionIds = @()
$disposalCreated = $false
$importCreated = $false
$pharmacistToken = $null
$internalKey = $null

function Read-Environment([string] $service) {
    $values = @{}
    Get-Content (Join-Path $root "services/$service/.env") | ForEach-Object {
        if ($_ -match '^([^#=]+)=(.*)$') { $values[$matches[1]] = $matches[2] }
    }
    return $values
}

function Invoke-Pims([string] $uri, [string] $token, [string] $method = 'GET', $body = $null, [bool] $internal = $false) {
    $headers = @{ Authorization = "Bearer $token" }
    if ($internal) { $headers['X-Internal-Key'] = $internalKey }
    $options = @{ Uri = $uri; Method = $method; Headers = $headers; TimeoutSec = 20 }
    if ($null -ne $body) { $options['Body'] = ConvertTo-Json -InputObject $body -Depth 8; $options['ContentType'] = 'application/json' }
    $response = Invoke-RestMethod @options
    if ($response.status -ne 'success') { throw "API failed: $uri" }
    return $response.data
}

function Get-Batches {
    $first = Invoke-Pims 'http://127.0.0.1:8003/api/v1/batches?page=1' $pharmacistToken
    $all = @($first.data)
    for ($page = 2; $page -le [int] $first.last_page; $page++) {
        $next = Invoke-Pims "http://127.0.0.1:8003/api/v1/batches?page=$page" $pharmacistToken
        $all += @($next.data)
    }
    return $all
}

function Invoke-Sql([string] $service, [string] $sql, [string] $value) {
    $environment = Read-Environment $service
    $connectionString = "Server=$($environment.DB_HOST);Database=$($environment.DB_DATABASE);User ID=$($environment.DB_USERNAME);Password=$($environment.DB_PASSWORD);Encrypt=False;TrustServerCertificate=True;Connection Timeout=5"
    $connection = [System.Data.SqlClient.SqlConnection]::new($connectionString)
    try {
        $connection.Open()
        $transaction = $connection.BeginTransaction()
        try {
            $command = $connection.CreateCommand()
            $command.Transaction = $transaction
            $command.CommandText = $sql
            [void] $command.Parameters.Add('@marker', [System.Data.SqlDbType]::VarChar, 50)
            $command.Parameters['@marker'].Value = $value
            [void] $command.ExecuteNonQuery()
            $transaction.Commit()
        } catch {
            $transaction.Rollback()
            throw
        }
    } finally { $connection.Dispose() }
}

try {
    $services = @(
        @('auth-service', 8001), @('catalog-service', 8002), @('inventory-service', 8003),
        @('prescription-service', 8004), @('report-service', 8005)
    )
    foreach ($service in $services) {
        $port = [int] $service[1]
        if (Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue) { continue }
        $public = Join-Path $root "services/$($service[0])/public"
        $arguments = @()
        if ($service[0] -eq 'report-service') { $arguments += @('-d', 'extension=zip', '-d', 'extension=gd', '-d', 'extension=fileinfo') }
        $arguments += @('-S', "127.0.0.1:$port", '-t', "`"$public`"", "`"$(Join-Path $public 'index.php')`"")
        $started += Start-Process -FilePath 'php' -ArgumentList $arguments -WindowStyle Hidden -PassThru
    }
    Start-Sleep -Seconds 2

    $internalKey = (Read-Environment 'inventory-service').INTERNAL_API_KEY
    $doctorLogin = Invoke-RestMethod -Uri 'http://127.0.0.1:8001/api/v1/auth/login' -Method POST -ContentType 'application/json' -Body (@{ email = 'doctor@pims.local'; password = 'Doctor@123' } | ConvertTo-Json)
    $pharmacistLogin = Invoke-RestMethod -Uri 'http://127.0.0.1:8001/api/v1/auth/login' -Method POST -ContentType 'application/json' -Body (@{ email = 'pharmacist@pims.local'; password = 'Pharmacist@123' } | ConvertTo-Json)
    $doctorToken = $doctorLogin.data.access_token
    $pharmacistToken = $pharmacistLogin.data.access_token
    if (!$doctorToken -or !$pharmacistToken) { throw 'Không đăng nhập được hai tài khoản mẫu.' }

    $initialBatches = @(Get-Batches)
    $valid = @($initialBatches | Where-Object { [decimal] $_.quantity_remaining -ge 1 -and [datetime] $_.expires_at -ge (Get-Date).Date })
    $group = $valid | Group-Object medicine_id | Where-Object Count -ge 2 | Select-Object -First 1
    if (!$group) { throw 'Cần ít nhất một thuốc có hai lô chưa hết hạn, mỗi lô còn ít nhất 1 đơn vị.' }
    $ordered = @($group.Group | Sort-Object expires_at, id)
    $firstBatch = $ordered[0]
    $secondBatch = $ordered[1]
    $medicineId = [int] $firstBatch.medicine_id
    $quantity = [decimal] $firstBatch.quantity_remaining + 1
    $otherMedicine = $valid | Where-Object { [int] $_.medicine_id -ne $medicineId } | Select-Object -First 1
    if (!$otherMedicine) { throw 'Cần một thuốc thứ hai để thử tình huống thiếu tồn.' }

    $prescription = Invoke-Pims 'http://127.0.0.1:8004/api/v1/prescriptions' $doctorToken 'POST' @{
        patient_name = $marker; patient_code = $marker; diagnosis = 'Kiểm thử đầu cuối'
        items = @(@{ medicine_id = $medicineId; quantity = $quantity; instructions = 'Kiểm thử FEFO' })
    }
    $prescriptionId = [int] $prescription.id
    $prescriptionIds += $prescriptionId
    $approved = Invoke-Pims "http://127.0.0.1:8004/api/v1/prescriptions/$prescriptionId/approve" $pharmacistToken 'POST'
    if ($approved.status -ne 'DISPENSED') { throw 'Đơn không chuyển sang DISPENSED.' }
    $afterApproval = @(Get-Batches)
    $firstAfter = $afterApproval | Where-Object id -eq $firstBatch.id | Select-Object -First 1
    $secondAfter = $afterApproval | Where-Object id -eq $secondBatch.id | Select-Object -First 1
    if ([decimal] $firstAfter.quantity_remaining -ne 0 -or [decimal] $secondAfter.quantity_remaining -ne ([decimal] $secondBatch.quantity_remaining - 1)) {
        throw 'Số lượng sau duyệt không đúng FEFO.'
    }
    $repeat = Invoke-Pims 'http://127.0.0.1:8003/internal/v1/stock/deduct-fefo' $pharmacistToken 'POST' @{
        prescription_id = $prescriptionId; items = @(@{ medicine_id = $medicineId; quantity = $quantity })
    } $true
    $afterRepeat = @(Get-Batches)
    if ([decimal] ($afterRepeat | Where-Object id -eq $secondBatch.id | Select-Object -First 1).quantity_remaining -ne [decimal] $secondAfter.quantity_remaining) {
        throw 'Gọi trừ kho lần hai đã làm thay đổi tồn.'
    }
    Write-Output 'PASS: kê đơn, duyệt FEFO hai lô, chống trừ lặp'

    $shortage = Invoke-Pims 'http://127.0.0.1:8004/api/v1/prescriptions' $doctorToken 'POST' @{
        patient_name = $marker; patient_code = $marker; diagnosis = 'Kiểm thử thiếu tồn'
        items = @(
            @{ medicine_id = $medicineId; quantity = 1; instructions = 'Kiểm thử' },
            @{ medicine_id = [int] $otherMedicine.medicine_id; quantity = 999999999; instructions = 'Kiểm thử' }
        )
    }
    $shortageId = [int] $shortage.id
    $prescriptionIds += $shortageId
    try {
        Invoke-Pims "http://127.0.0.1:8004/api/v1/prescriptions/$shortageId/approve" $pharmacistToken 'POST' | Out-Null
        throw 'Đơn thiếu tồn vẫn được duyệt.'
    } catch {
        if (!$_.Exception.Response -or [int] $_.Exception.Response.StatusCode -ne 409) { throw }
    }
    $shortageState = Invoke-Pims "http://127.0.0.1:8004/api/v1/prescriptions/$shortageId" $doctorToken
    if ($shortageState.status -ne 'NEW') { throw 'Đơn thiếu tồn không giữ trạng thái NEW.' }
    $rejected = Invoke-Pims "http://127.0.0.1:8004/api/v1/prescriptions/$shortageId/reject" $pharmacistToken 'POST' @{ reason = 'Kiểm thử thiếu tồn' }
    if ($rejected.status -ne 'REJECTED') { throw 'Không từ chối được đơn.' }
    Write-Output 'PASS: thiếu tồn không trừ kho, từ chối đơn có lý do'

    $disposalBatch = $afterRepeat | Where-Object { [decimal] $_.quantity_remaining -ge 1 } | Select-Object -First 1
    $disposalCreated = $true
    $disposal = Invoke-Pims 'http://127.0.0.1:8003/api/v1/disposal-receipts' $pharmacistToken 'POST' @{
        receipt_code = $disposalCode; disposed_at = (Get-Date).ToString('yyyy-MM-ddTHH:mm:ss')
        reason = $marker; items = @(@{ batch_id = [int] $disposalBatch.id; quantity = 1 })
    }
    $afterDisposal = @(Get-Batches)
    if ([decimal] ($afterDisposal | Where-Object id -eq $disposalBatch.id | Select-Object -First 1).quantity_remaining -ne ([decimal] $disposalBatch.quantity_remaining - 1)) {
        throw 'Xuất hủy không trừ đúng số lượng.'
    }
    Write-Output 'PASS: xuất hủy theo lô và ghi phiếu'

    $suppliers = Invoke-Pims 'http://127.0.0.1:8002/api/v1/suppliers' $pharmacistToken
    $supplier = $suppliers.data | Select-Object -First 1
    $medicine = Invoke-Pims "http://127.0.0.1:8002/api/v1/medicines/$medicineId" $pharmacistToken
    if (!$supplier -or !$medicine.base_unit_id) { throw 'Missing supplier or medicine base unit for import test.' }
    $importCreated = $true
    $importPayload = @{
        receipt_code = $importCode; supplier_id = [int] $supplier.id
        imported_at = (Get-Date).ToString('yyyy-MM-ddTHH:mm:ss')
        items = @(@{
            medicine_id = $medicineId; unit_id = [int] $medicine.base_unit_id
            batch_code = $importBatchCode; manufactured_at = (Get-Date).AddDays(-1).ToString('yyyy-MM-dd')
            expires_at = (Get-Date).AddDays(365).ToString('yyyy-MM-dd')
            quantity = 2; unit_cost = 0
        })
    }
    $receipt = Invoke-Pims 'http://127.0.0.1:8003/api/v1/import-receipts' $pharmacistToken 'POST' $importPayload
    $importedBatch = $receipt.details[0].batch
    if ($importedBatch.batch_code -ne $importBatchCode -or [decimal] $importedBatch.quantity_remaining -ne 2) {
        throw 'Import receipt did not create the expected batch.'
    }
    try {
        Invoke-Pims 'http://127.0.0.1:8003/api/v1/import-receipts' $pharmacistToken 'POST' $importPayload | Out-Null
        throw 'Duplicate receipt was accepted.'
    } catch {
        if (!$_.Exception.Response -or [int] $_.Exception.Response.StatusCode -ne 422) { throw }
    }
    Write-Output 'PASS: nhap kho tao lo va so luong ton'

    $alerts = Invoke-Pims 'http://127.0.0.1:8003/api/v1/alerts' $pharmacistToken
    $report = Invoke-Pims 'http://127.0.0.1:8005/api/v1/reports/stock?from=2026-01-01&to=2026-12-31' $pharmacistToken
    if ($null -eq $alerts.summary -or $null -eq $report.stock.rows) { throw 'Cảnh báo hoặc báo cáo không trả dữ liệu.' }
    Write-Output 'PASS: cảnh báo và báo cáo đọc dữ liệu sau biến động kho'
} finally {
    try {
        if ($pharmacistToken) {
            foreach ($prescriptionId in $prescriptionIds) {
                $status = Invoke-Pims "http://127.0.0.1:8003/internal/v1/stock/prescriptions/$prescriptionId" $pharmacistToken 'GET' $null $true
                if ($status.status -eq 'DEDUCTED') {
                    Invoke-Pims 'http://127.0.0.1:8003/internal/v1/stock/restore' $pharmacistToken 'POST' @{ prescription_id = $prescriptionId } $true | Out-Null
                }
                Invoke-Sql 'inventory-service' @'
DELETE FROM stock_movements WHERE reference_type = 'PRESCRIPTION' AND reference_id = @marker;
DELETE FROM prescription_stock_operations WHERE prescription_id = @marker;
'@ ([string] $prescriptionId)
            }
        }
        if ($disposalCreated) {
            Invoke-Sql 'inventory-service' @'
UPDATE batch SET quantity_remaining = batch.quantity_remaining + detail.quantity
FROM batches AS batch JOIN disposal_details AS detail ON detail.batch_id = batch.id
JOIN disposal_receipts AS receipt ON receipt.id = detail.disposal_receipt_id
WHERE receipt.receipt_code = @marker;
DELETE FROM stock_movements WHERE reference_type = 'DISPOSAL_RECEIPT' AND reference_id IN (SELECT id FROM disposal_receipts WHERE receipt_code = @marker);
DELETE FROM disposal_details WHERE disposal_receipt_id IN (SELECT id FROM disposal_receipts WHERE receipt_code = @marker);
DELETE FROM disposal_receipts WHERE receipt_code = @marker;
'@ $disposalCode
        }
        if ($importCreated) {
            Invoke-Sql 'inventory-service' @'
DELETE FROM stock_movements WHERE reference_type = 'IMPORT_RECEIPT' AND reference_id IN (SELECT id FROM import_receipts WHERE receipt_code = @marker);
DELETE FROM batches WHERE import_receipt_detail_id IN (SELECT id FROM import_receipt_details WHERE import_receipt_id IN (SELECT id FROM import_receipts WHERE receipt_code = @marker));
DELETE FROM import_receipt_details WHERE import_receipt_id IN (SELECT id FROM import_receipts WHERE receipt_code = @marker);
DELETE FROM import_receipts WHERE receipt_code = @marker;
'@ $importCode
        }
        if ($prescriptionIds.Count -gt 0) {
            Invoke-Sql 'prescription-service' @'
DELETE FROM prescription_details WHERE prescription_id IN (SELECT id FROM prescriptions WHERE patient_code = @marker);
DELETE FROM prescriptions WHERE patient_code = @marker;
'@ $marker
        }
        if ($initialBatches) {
            $finalBatches = @(Get-Batches)
            foreach ($initialBatch in $initialBatches) {
                $finalBatch = $finalBatches | Where-Object id -eq $initialBatch.id | Select-Object -First 1
                if (!$finalBatch -or [decimal] $finalBatch.quantity_remaining -ne [decimal] $initialBatch.quantity_remaining) {
                    throw "Batch $($initialBatch.id) was not restored to its original quantity."
                }
            }
            if ($finalBatches | Where-Object batch_code -eq $importBatchCode) {
                throw 'Test import batch still exists after cleanup.'
            }
        }
        Write-Output 'Dọn dữ liệu kiểm thử: hoàn tất, tồn kho khớp ban đầu'
    } finally {
        foreach ($process in $started) { Stop-Process -Id $process.Id -ErrorAction SilentlyContinue }
    }
}
