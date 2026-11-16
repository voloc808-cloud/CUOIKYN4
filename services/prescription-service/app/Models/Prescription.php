<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Prescription extends Model
{
    public const NEW = 'NEW';
    public const APPROVED = 'APPROVED';
    public const DISPENSED = 'DISPENSED';
    public const REJECTED = 'REJECTED';

    protected $fillable = ['prescription_code', 'doctor_id', 'patient_name', 'patient_code', 'diagnosis', 'status', 'rejection_reason', 'approved_by', 'approved_at', 'dispensed_at'];
    protected $casts = ['approved_at' => 'datetime', 'dispensed_at' => 'datetime'];
    public function details(): HasMany { return $this->hasMany(PrescriptionDetail::class); }
}
