<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
final class DisposalReceipt extends Model
{
    protected $fillable = ['receipt_code', 'disposed_at', 'reason', 'created_by'];
    protected $casts = ['disposed_at' => 'datetime'];
    public function details(): HasMany { return $this->hasMany(DisposalDetail::class); }
}
