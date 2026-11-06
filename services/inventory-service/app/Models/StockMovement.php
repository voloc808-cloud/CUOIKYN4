<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StockMovement extends Model
{
    public const IMPORT = 'IMPORT';
    public const EXPORT_PRESCRIPTION = 'EXPORT_PRESCRIPTION';
    public const RESTORE_PRESCRIPTION = 'RESTORE_PRESCRIPTION';
    public const EXPORT_DISPOSAL = 'EXPORT_DISPOSAL';

    protected $fillable = ['batch_id', 'medicine_id', 'movement_type', 'quantity', 'reference_type', 'reference_id', 'performed_by', 'note'];
    protected $casts = ['quantity' => 'decimal:3'];

    public function batch(): BelongsTo { return $this->belongsTo(Batch::class); }
}
