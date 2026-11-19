<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Role extends Model
{
    public const ADMIN = 'ADMIN';
    public const DOCTOR = 'DOCTOR';
    public const PHARMACIST = 'PHARMACIST';

    protected $fillable = ['code', 'name'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
