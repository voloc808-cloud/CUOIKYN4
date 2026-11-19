<?php

namespace App\Repositories;

use App\Models\Batch;
use App\Models\ImportReceipt;
use App\Models\ImportReceiptDetail;
use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class InventoryRepository
{
    public function receipts(string $search): LengthAwarePaginator
    {
        return ImportReceipt::withCount('details')
            ->when($search !== '', fn ($query) => $query->where('receipt_code', 'like', "%{$search}%")->orWhere('invoice_number', 'like', "%{$search}%"))
            ->latest('imported_at')->paginate(15);
    }

    public function receipt(int $id): ImportReceipt
    {
        return ImportReceipt::with(['details.batch'])->findOrFail($id);
    }

    public function batches(string $search): LengthAwarePaginator
    {
        return Batch::when($search !== '', fn ($query) => $query->where('batch_code', 'like', "%{$search}%"))
            ->orderBy('expires_at')->paginate(15);
    }

    public function movements(): LengthAwarePaginator
    {
        return StockMovement::with('batch')->latest()->paginate(20);
    }

    public function hasBatches(int $medicineId): bool
    {
        return Batch::where('medicine_id', $medicineId)->exists();
    }

    public function batchCodeExists(int $medicineId, string $batchCode): bool
    {
        return Batch::where('medicine_id', $medicineId)->where('batch_code', $batchCode)->exists();
    }

    public function createReceipt(array $data): ImportReceipt { return ImportReceipt::create($data); }
    public function createDetail(array $data): ImportReceiptDetail { return ImportReceiptDetail::create($data); }
    public function createBatch(array $data): Batch { return Batch::create($data); }
    public function createMovement(array $data): StockMovement { return StockMovement::create($data); }
}
