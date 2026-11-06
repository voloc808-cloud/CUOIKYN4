<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class PrescriptionStockOperation extends Model
{
    public const DEDUCTED = 'DEDUCTED';
    public const RESTORED = 'RESTORED';

    protected $fillable = ['prescription_id', 'status', 'result_json'];
}
