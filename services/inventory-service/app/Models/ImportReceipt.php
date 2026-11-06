<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ImportReceipt extends Model
{
    protected $fillable = ['receipt_code', 'supplier_id', 'imported_at', 'invoice_number', 'note', 'created_by'];
    protected $casts = ['imported_at' => 'datetime'];

    public function details(): HasMany
    {
        return $this->hasMany(ImportReceiptDetail::class);
    }
}
