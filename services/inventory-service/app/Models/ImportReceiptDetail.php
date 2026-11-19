<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class ImportReceiptDetail extends Model
{
    protected $fillable = ['import_receipt_id', 'medicine_id', 'unit_id', 'batch_code', 'manufactured_at', 'expires_at', 'quantity', 'unit_cost'];
    protected $casts = ['manufactured_at' => 'date', 'expires_at' => 'date', 'quantity' => 'decimal:3', 'unit_cost' => 'decimal:2'];

    public function receipt(): BelongsTo { return $this->belongsTo(ImportReceipt::class, 'import_receipt_id'); }
    public function batch(): HasOne { return $this->hasOne(Batch::class); }
}
