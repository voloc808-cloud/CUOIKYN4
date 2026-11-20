<?php

declare(strict_types=1);

namespace Pims\Common\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Pims\Common\Http\ApiResponse;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWT;

final class VerifyJwt
{
    public function __construct(private readonly JWT $jwt)
    {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        try {
            $payload = $this->jwt->parseToken()->getPayload();
        } catch (JWTException) {
            return ApiResponse::error('Token không hợp lệ hoặc đã hết hạn', [], 401, 'UNAUTHENTICATED');
        }

        $request->attributes->set('auth_user', (object) [
            'id' => (int) $payload->get('sub'),
            'role_code' => (string) $payload->get('role'),
        ]);

        return $next($request);
    }
}
