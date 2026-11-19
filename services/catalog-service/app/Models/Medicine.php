<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Medicine extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'base_unit_id', 'code', 'name', 'active_ingredient', 'strength',
        'dosage_form', 'minimum_stock', 'is_active',
    ];

    protected $casts = ['minimum_stock' => 'decimal:3', 'is_active' => 'boolean'];

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(UnitConversion::class);
    }
}
