<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Batch extends Model
{
    protected $fillable = ['import_receipt_detail_id', 'medicine_id', 'unit_id', 'batch_code', 'manufactured_at', 'expires_at', 'quantity_imported', 'quantity_remaining'];
    protected $casts = ['manufactured_at' => 'date', 'expires_at' => 'date', 'quantity_imported' => 'decimal:3', 'quantity_remaining' => 'decimal:3'];

    public function detail(): BelongsTo { return $this->belongsTo(ImportReceiptDetail::class, 'import_receipt_detail_id'); }
    public function movements(): HasMany { return $this->hasMany(StockMovement::class); }
}
