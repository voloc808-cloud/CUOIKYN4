<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
final class AlertSetting extends Model
{
    protected $fillable = ['expiry_warning_days'];
    protected $casts = ['expiry_warning_days' => 'integer'];
}
