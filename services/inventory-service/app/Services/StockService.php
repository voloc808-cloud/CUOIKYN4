<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Batch;
use App\Models\PrescriptionStockOperation;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use App\Support\FefoAllocator;
use App\Support\IdempotentDeduction;

final class StockService
{
    public function __construct(private readonly FefoAllocator $allocator, private readonly IdempotentDeduction $idempotency) {}

    public function check(array $data): array
    {
        $items = $this->normalize($data['items']);
        $shortages = [];
        foreach ($items as $medicineId => $quantity) {
            $available = (float) Batch::where('medicine_id', $medicineId)
                ->whereDate('expires_at', '>=', \Illuminate\Support\Carbon::now()->toDateString())
                ->where('quantity_remaining', '>', 0)
                ->sum('quantity_remaining');
            if ($available < $quantity) {
                $shortages[] = compact('medicineId', 'quantity', 'available');
            }
        }

        return ['available' => $shortages === [], 'shortages' => array_map(fn (array $item) => [
            'medicine_id' => $item['medicineId'],
            'required' => $item['quantity'],
            'available' => $item['available'],
        ], $shortages)];
    }

    public function deductionStatus(int $prescriptionId): array
    {
        $operation = PrescriptionStockOperation::where('prescription_id', $prescriptionId)->first();

        return ['prescription_id' => $prescriptionId, 'status' => $operation?->status ?? 'NOT_FOUND'];
    }

    public function deductFefo(array $data, int $actorId): array
    {
        return DB::transaction(function () use ($data, $actorId): array {
            $prescriptionId = (int) $data['prescription_id'];
            $operation = PrescriptionStockOperation::where('prescription_id', $prescriptionId)->lockForUpdate()->first();
            if ($operation) return $this->idempotency->execute(['status' => $operation->status, 'result' => json_decode((string) $operation->result_json, true) ?: ['prescription_id' => $prescriptionId, 'allocations' => []]], fn () => []);

            $items = $this->normalize($data['items']);
            $batchesByMedicine = [];
            foreach ($items as $medicineId => $required) {
                $batches = Batch::where('medicine_id', $medicineId)
                    ->whereDate('expires_at', '>=', \Illuminate\Support\Carbon::now()->toDateString())
                    ->where('quantity_remaining', '>', 0)
                    ->orderBy('expires_at')->orderBy('id')->lockForUpdate()->get();
                if ((float) $batches->sum('quantity_remaining') < $required) {
                    throw new BusinessException("Thuốc {$medicineId} không đủ tồn khả dụng", 'INSUFFICIENT_STOCK', 409);
                }
                $batchesByMedicine[$medicineId] = $batches;
            }

            $allocations = [];
            foreach ($items as $medicineId => $required) {
                $batches = $batchesByMedicine[$medicineId];
                $plan = $this->allocator->allocate($batches->map(fn ($batch) => ['id' => $batch->id, 'expires_at' => $batch->expires_at->toDateString(), 'quantity_remaining' => (float) $batch->quantity_remaining])->all(), $required);
                foreach ($plan as $allocation) {
                    $batch = $batches->firstWhere('id', $allocation['batch_id']);
                    $quantity = $allocation['quantity'];
                    $batch->decrement('quantity_remaining', $quantity);
                    StockMovement::create([
                        'batch_id' => $batch->id, 'medicine_id' => $medicineId,
                        'movement_type' => StockMovement::EXPORT_PRESCRIPTION, 'quantity' => $quantity,
                        'reference_type' => 'PRESCRIPTION', 'reference_id' => $prescriptionId,
                        'performed_by' => $actorId,
                    ]);
                    $allocations[] = ['medicine_id' => $medicineId, 'batch_id' => $batch->id, 'batch_code' => $batch->batch_code, 'quantity' => $quantity];
                }
            }

            $result = ['prescription_id' => $prescriptionId, 'allocations' => $allocations];
            PrescriptionStockOperation::updateOrCreate(
                ['prescription_id' => $prescriptionId],
                ['status' => PrescriptionStockOperation::DEDUCTED, 'result_json' => json_encode($result)]
            );
            return $result;
        }, 3);
    }

    public function restore(array $data, int $actorId): array
    {
        return DB::transaction(function () use ($data, $actorId): array {
            $prescriptionId = (int) $data['prescription_id'];
            $operation = PrescriptionStockOperation::where('prescription_id', $prescriptionId)->lockForUpdate()->first();
            if (!$operation) throw new BusinessException('Chưa có giao dịch xuất kho của đơn thuốc', 'DEDUCTION_NOT_FOUND', 404);
            if ($operation->status === PrescriptionStockOperation::RESTORED) return ['prescription_id' => $prescriptionId, 'restored' => true];

            $movements = StockMovement::where('reference_type', 'PRESCRIPTION')
                ->where('reference_id', $prescriptionId)
                ->where('movement_type', StockMovement::EXPORT_PRESCRIPTION)->lockForUpdate()->get();
            foreach ($movements as $movement) {
                $batch = Batch::whereKey($movement->batch_id)->lockForUpdate()->firstOrFail();
                $batch->increment('quantity_remaining', (float) $movement->quantity);
                StockMovement::create([
                    'batch_id' => $batch->id, 'medicine_id' => $movement->medicine_id,
                    'movement_type' => StockMovement::RESTORE_PRESCRIPTION, 'quantity' => $movement->quantity,
                    'reference_type' => 'PRESCRIPTION', 'reference_id' => $prescriptionId,
                    'performed_by' => $actorId,
                ]);
            }
            $operation->update(['status' => PrescriptionStockOperation::RESTORED]);
            return ['prescription_id' => $prescriptionId, 'restored' => true];
        }, 3);
    }

    private function normalize(array $items): array
    {
        $normalized = [];
        foreach ($items as $item) {
            $medicineId = (int) $item['medicine_id'];
            $normalized[$medicineId] = ($normalized[$medicineId] ?? 0) + (float) $item['quantity'];
        }
        return $normalized;
    }
}
