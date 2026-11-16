<?php

namespace App\Repositories;

use App\Models\Unit;
use App\Models\UnitConversion;
use Illuminate\Database\Eloquent\Collection;

final class UnitRepository
{
    public function all(): Collection
    {
        return Unit::orderBy('name')->get();
    }

    public function create(array $data): Unit
    {
        return Unit::create($data);
    }

    public function conversions(): Collection
    {
        return UnitConversion::with(['medicine', 'fromUnit', 'toUnit'])->orderBy('medicine_id')->get();
    }

    public function createConversion(array $data): UnitConversion
    {
        return UnitConversion::create($data)->load(['medicine', 'fromUnit', 'toUnit']);
    }
}
