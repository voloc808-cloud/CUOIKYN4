<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PrescriptionDetail extends Model
{
    protected $fillable = ['prescription_id', 'medicine_id', 'quantity', 'instructions'];
    protected $casts = ['quantity' => 'decimal:3'];
    public function prescription(): BelongsTo { return $this->belongsTo(Prescription::class); }
}
