<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Pims\Common\Http\ApiResponse;
use Throwable;

final class HealthController extends Controller
{
    public function __invoke()
    {
        try {
            DB::select('SELECT 1 AS healthy');
            return ApiResponse::success(['service' => 'inventory-service', 'database' => 'up']);
        } catch (Throwable) {
            return ApiResponse::error('Cơ sở dữ liệu kho không khả dụng', ['database' => 'down'], 503);
        }
    }
}
