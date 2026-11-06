<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'contact_person', 'phone', 'email', 'tax_code', 'address', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];
}
