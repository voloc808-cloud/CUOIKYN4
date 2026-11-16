<?php

namespace App\Http\Controllers;

use Pims\Common\Http\ApiResponse;

final class HealthController extends Controller
{
    public function __invoke()
    {
        return ApiResponse::success(['service' => 'report-service', 'database' => 'not-applicable']);
    }
}
