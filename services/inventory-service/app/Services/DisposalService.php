<?php
namespace App\Services;
use App\Exceptions\BusinessException;
use App\Models\Batch;
use App\Models\DisposalReceipt;
use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
final class DisposalService
{
    public function paginate(): LengthAwarePaginator { return DisposalReceipt::with(['details.batch'])->latest('disposed_at')->paginate(15); }
    public function find(int $id): DisposalReceipt { return DisposalReceipt::with(['details.batch'])->findOrFail($id); }
    public function create(array $data, int $actorId): DisposalReceipt
    {
        return DB::transaction(function () use ($data, $actorId): DisposalReceipt {
            $locked = [];
            foreach ($data['items'] as $item) {
                $batch = Batch::whereKey($item['batch_id'])->lockForUpdate()->firstOrFail();
                if ((float) $batch->quantity_remaining < (float) $item['quantity']) throw new BusinessException("Lô {$batch->batch_code} không đủ tồn để xuất hủy", 'INSUFFICIENT_STOCK', 409);
                $locked[] = [$batch, (float) $item['quantity']];
            }
            $receipt = DisposalReceipt::create(['receipt_code' => $data['receipt_code'], 'disposed_at' => $data['disposed_at'], 'reason' => $data['reason'], 'created_by' => $actorId]);
            foreach ($locked as [$batch, $quantity]) {
                $receipt->details()->create(['batch_id' => $batch->id, 'quantity' => $quantity]);
                $batch->decrement('quantity_remaining', $quantity);
                StockMovement::create(['batch_id' => $batch->id, 'medicine_id' => $batch->medicine_id, 'movement_type' => StockMovement::EXPORT_DISPOSAL, 'quantity' => $quantity, 'reference_type' => 'DISPOSAL_RECEIPT', 'reference_id' => $receipt->id, 'performed_by' => $actorId, 'note' => $data['reason']]);
            }
            return $receipt->load(['details.batch']);
        }, 3);
    }
}
