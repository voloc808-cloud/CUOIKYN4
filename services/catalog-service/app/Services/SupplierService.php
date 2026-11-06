<?php

namespace App\Services;

use App\Models\Supplier;
use App\Repositories\SupplierRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class SupplierService
{
    public function __construct(private readonly SupplierRepository $suppliers)
    {
    }

    public function paginate(string $search): LengthAwarePaginator
    {
        return $this->suppliers->paginate($search);
    }

    public function find(int $id): Supplier
    {
        return $this->suppliers->findOrFail($id);
    }

    public function create(array $data): Supplier
    {
        return $this->suppliers->create($data);
    }

    public function update(int $id, array $data): Supplier
    {
        return $this->suppliers->update($this->suppliers->findOrFail($id), $data);
    }

    public function delete(int $id): void
    {
        $this->suppliers->findOrFail($id)->delete();
    }
}
