<?php

namespace App\Repositories;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class SupplierRepository
{
    public function paginate(string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Supplier::when($search !== '', fn ($query) => $query->where(fn ($nested) => $nested
            ->where('code', 'like', "%{$search}%")
            ->orWhere('name', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Supplier
    {
        return Supplier::findOrFail($id);
    }

    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);
        return $supplier->fresh();
    }
}
