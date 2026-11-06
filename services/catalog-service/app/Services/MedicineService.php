<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Medicine;
use App\Repositories\MedicineRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class MedicineService
{
    public function __construct(private readonly MedicineRepository $medicines)
    {
    }

    public function paginate(string $search): LengthAwarePaginator
    {
        return $this->medicines->paginate($search);
    }

    public function find(int $id): Medicine
    {
        return $this->medicines->findOrFail($id);
    }

    public function stockThresholds(): array
    {
        return Medicine::where('is_active', true)->get(['id', 'code', 'name', 'minimum_stock'])->toArray();
    }

    public function create(array $data): Medicine
    {
        return $this->medicines->create($data);
    }

    public function update(int $id, array $data): Medicine
    {
        return $this->medicines->update($this->medicines->findOrFail($id), $data);
    }

    public function delete(int $id): void
    {
        $this->medicines->findOrFail($id);
        throw new BusinessException(
            'Chưa thể xóa thuốc trước khi dịch vụ kho xác nhận thuốc không có lô',
            'INVENTORY_CHECK_REQUIRED',
            503
        );
    }

    public function deleteVerified(int $id): void
    {
        $this->medicines->findOrFail($id)->delete();
    }
}
