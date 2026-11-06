<?php

declare(strict_types=1);

namespace Pims\Common\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class CorrelationId
{
    public function handle(Request $request, Closure $next): mixed
    {
        $correlationId = $request->header('X-Correlation-ID', (string) Str::uuid());
        $request->headers->set('X-Correlation-ID', $correlationId);
        app()->instance('correlation_id', $correlationId);

        $response = $next($request);
        $response->headers->set('X-Correlation-ID', $correlationId);

        return $response;
    }
}
