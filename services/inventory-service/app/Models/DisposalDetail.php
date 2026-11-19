<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
final class DisposalDetail extends Model
{
    protected $fillable = ['disposal_receipt_id', 'batch_id', 'quantity'];
    protected $casts = ['quantity' => 'decimal:3'];
    public function batch(): BelongsTo { return $this->belongsTo(Batch::class); }
}
