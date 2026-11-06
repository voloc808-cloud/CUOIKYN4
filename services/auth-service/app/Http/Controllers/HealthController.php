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
            return ApiResponse::success(['service' => 'auth-service', 'database' => 'up']);
        } catch (Throwable) {
            return ApiResponse::error('Cơ sở dữ liệu xác thực không khả dụng', ['database' => 'down'], 503);
        }
    }
}
