<?php

namespace App\Services;

use App\Models\Unit;
use App\Models\UnitConversion;
use App\Repositories\UnitRepository;
use Illuminate\Database\Eloquent\Collection;

final class UnitService
{
    public function __construct(private readonly UnitRepository $units)
    {
    }

    public function all(): Collection
    {
        return $this->units->all();
    }

    public function create(array $data): Unit
    {
        return $this->units->create($data);
    }

    public function conversions(): Collection
    {
        return $this->units->conversions();
    }

    public function createConversion(array $data): UnitConversion
    {
        return $this->units->createConversion($data);
    }
}
