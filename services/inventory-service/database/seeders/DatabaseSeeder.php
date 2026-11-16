<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\ImportReceipt;
use App\Models\ImportReceiptDetail;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $receipt = ImportReceipt::updateOrCreate(['receipt_code' => 'PN-SEED-001'], [
            'supplier_id' => 1,
            'imported_at' => Carbon::now()->subDays(10),
            'invoice_number' => 'HD-SEED-001',
            'note' => 'Dữ liệu mẫu Giai đoạn 4',
            'created_by' => 2,
        ]);

        for ($medicineId = 1; $medicineId <= 20; $medicineId++) {
            foreach ([1, 2] as $sequence) {
                $batchCode = sprintf('LOT-%03d-%d', $medicineId, $sequence);
                $expiresAt = match (true) {
                    $medicineId <= 3 && $sequence === 1 => Carbon::today()->subDays(20 + $medicineId),
                    $medicineId <= 8 && $sequence === 1 => Carbon::today()->addDays(10 + $medicineId),
                    default => Carbon::today()->addMonths(8 + $sequence),
                };
                $detail = ImportReceiptDetail::updateOrCreate(
                    ['import_receipt_id' => $receipt->id, 'medicine_id' => $medicineId, 'batch_code' => $batchCode],
                    ['unit_id' => 3, 'manufactured_at' => Carbon::today()->subMonths(6), 'expires_at' => $expiresAt, 'quantity' => 200, 'unit_cost' => 1000 + $medicineId * 100]
                );
                $batch = Batch::updateOrCreate(
                    ['medicine_id' => $medicineId, 'batch_code' => $batchCode],
                    ['import_receipt_detail_id' => $detail->id, 'unit_id' => 3, 'manufactured_at' => $detail->manufactured_at, 'expires_at' => $expiresAt, 'quantity_imported' => 200, 'quantity_remaining' => 200]
                );
                StockMovement::updateOrCreate(
                    ['batch_id' => $batch->id, 'movement_type' => StockMovement::IMPORT, 'reference_type' => 'IMPORT_RECEIPT', 'reference_id' => $receipt->id],
                    ['medicine_id' => $medicineId, 'quantity' => 200, 'performed_by' => 2, 'note' => 'Nhập tồn đầu kỳ']
                );
            }
        }
    }
}
