<?php

namespace App\Repositories;

use App\Models\Medicine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class MedicineRepository
{
    public function paginate(string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Medicine::with(['baseUnit', 'conversions.fromUnit', 'conversions.toUnit'])
            ->when($search !== '', fn ($query) => $query->where(fn ($nested) => $nested
                ->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('active_ingredient', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Medicine
    {
        return Medicine::with(['baseUnit', 'conversions.fromUnit', 'conversions.toUnit'])->findOrFail($id);
    }

    public function create(array $data): Medicine
    {
        return Medicine::create($data)->load('baseUnit');
    }

    public function update(Medicine $medicine, array $data): Medicine
    {
        $medicine->update($data);
        return $medicine->fresh(['baseUnit', 'conversions.fromUnit', 'conversions.toUnit']);
    }
}
