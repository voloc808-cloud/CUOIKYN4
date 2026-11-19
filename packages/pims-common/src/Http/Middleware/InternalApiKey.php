<?php

declare(strict_types=1);

namespace Pims\Common\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Pims\Common\Http\ApiResponse;

final class InternalApiKey
{
    public function handle(Request $request, Closure $next): mixed
    {
        $expected = (string) env('INTERNAL_API_KEY');
        $provided = (string) $request->header('X-Internal-Key');

        if ($expected === '' || !hash_equals($expected, $provided)) {
            return ApiResponse::error('Khóa API nội bộ không hợp lệ', [], 401, 'INVALID_INTERNAL_KEY');
        }

        return $next($request);
    }
}
