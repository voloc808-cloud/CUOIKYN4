<?php

namespace App\Services;

use App\Clients\CatalogClient;
use App\Exceptions\BusinessException;
use App\Models\ImportReceipt;
use App\Models\StockMovement;
use App\Repositories\InventoryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Support\BatchRules;

final class InventoryService
{
    public function __construct(
        private readonly InventoryRepository $inventory,
        private readonly CatalogClient $catalog,
        private readonly BatchRules $batchRules
    ) {
    }

    public function receipts(string $search, string $token): LengthAwarePaginator
    {
        $paginator = $this->inventory->receipts($search);
        $suppliers = [];
        $paginator->getCollection()->transform(function ($receipt) use (&$suppliers, $token) {
            $supplierId = (int) $receipt->supplier_id;
            $suppliers[$supplierId] ??= $this->catalog->supplier($supplierId, $token);
            $receipt->setAttribute('supplier', $suppliers[$supplierId]);
            return $receipt;
        });
        return $paginator;
    }

    public function receipt(int $id, string $token): ImportReceipt
    {
        $receipt = $this->inventory->receipt($id);
        $receipt->setAttribute('supplier', $this->catalog->supplier((int) $receipt->supplier_id, $token));
        $medicines = [];
        $receipt->details->each(function ($detail) use (&$medicines, $token): void {
            $medicineId = (int) $detail->medicine_id;
            $medicines[$medicineId] ??= $this->catalog->medicine($medicineId, $token);
            $detail->setAttribute('medicine', $medicines[$medicineId]);
        });
        return $receipt;
    }
    public function batches(string $search): LengthAwarePaginator { return $this->enrich($this->inventory->batches($search)); }
    public function movements(): LengthAwarePaginator { return $this->enrich($this->inventory->movements()); }
    public function hasBatches(int $medicineId): bool { return $this->inventory->hasBatches($medicineId); }

    public function import(array $data, int $actorId, string $token): ImportReceipt
    {
        $this->catalog->supplier((int) $data['supplier_id'], $token);
        $prepared = [];
        $requestKeys = [];

        foreach ($data['items'] as $item) {
            $key = $item['medicine_id'].'|'.mb_strtoupper($item['batch_code']);
            $this->batchRules->validate($item, fn (int $medicineId, string $batchCode) => isset($requestKeys[$key]) || $this->inventory->batchCodeExists($medicineId, $batchCode));
            $requestKeys[$key] = true;

            $medicine = $this->catalog->medicine((int) $item['medicine_id'], $token);
            $factor = $this->conversionFactor($medicine, (int) $item['unit_id']);
            $prepared[] = [$item, $medicine, (float) $item['quantity'] * $factor];
        }

        return DB::transaction(function () use ($data, $actorId, $prepared): ImportReceipt {
            $receipt = $this->inventory->createReceipt([
                'receipt_code' => $data['receipt_code'],
                'supplier_id' => $data['supplier_id'],
                'imported_at' => $data['imported_at'],
                'invoice_number' => $data['invoice_number'] ?? null,
                'note' => $data['note'] ?? null,
                'created_by' => $actorId,
            ]);

            foreach ($prepared as [$item, $medicine, $normalizedQuantity]) {
                $detail = $this->inventory->createDetail([
                    'import_receipt_id' => $receipt->id,
                    'medicine_id' => $item['medicine_id'],
                    'unit_id' => $item['unit_id'],
                    'batch_code' => $item['batch_code'],
                    'manufactured_at' => $item['manufactured_at'],
                    'expires_at' => $item['expires_at'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'] ?? 0,
                ]);
                $batch = $this->inventory->createBatch([
                    'import_receipt_detail_id' => $detail->id,
                    'medicine_id' => $item['medicine_id'],
                    'unit_id' => $medicine['base_unit_id'],
                    'batch_code' => $item['batch_code'],
                    'manufactured_at' => $item['manufactured_at'],
                    'expires_at' => $item['expires_at'],
                    'quantity_imported' => $normalizedQuantity,
                    'quantity_remaining' => $normalizedQuantity,
                ]);
                $this->inventory->createMovement([
                    'batch_id' => $batch->id,
                    'medicine_id' => $item['medicine_id'],
                    'movement_type' => StockMovement::IMPORT,
                    'quantity' => $normalizedQuantity,
                    'reference_type' => 'IMPORT_RECEIPT',
                    'reference_id' => $receipt->id,
                    'performed_by' => $actorId,
                    'note' => $data['note'] ?? null,
                ]);
            }

            return $receipt->load(['details.batch']);
        });
    }

    private function conversionFactor(array $medicine, int $unitId): float
    {
        if ((int) $medicine['base_unit_id'] === $unitId) {
            return 1;
        }
        foreach ($medicine['conversions'] ?? [] as $conversion) {
            if ((int) $conversion['from_unit_id'] === $unitId && (int) $conversion['to_unit_id'] === (int) $medicine['base_unit_id']) {
                return (float) $conversion['factor'];
            }
        }
        throw new BusinessException('Chưa cấu hình quy đổi về đơn vị cơ sở cho thuốc', 'UNIT_CONVERSION_NOT_FOUND');
    }

    private function enrich(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $token = (string) request()->bearerToken();
        $cache = [];
        $paginator->getCollection()->transform(function ($item) use (&$cache, $token) {
            $medicineId = (int) $item->medicine_id;
            $cache[$medicineId] ??= $this->catalog->medicine($medicineId, $token);
            $item->setAttribute('medicine', $cache[$medicineId]);
            return $item;
        });
        return $paginator;
    }
}
