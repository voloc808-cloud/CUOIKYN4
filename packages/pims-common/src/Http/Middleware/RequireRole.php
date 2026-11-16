<?php

declare(strict_types=1);

namespace Pims\Common\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Pims\Common\Http\ApiResponse;

final class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->attributes->get('auth_user');
        $role = null;
        if (is_object($user)) {
            $role = $user->role_code ?? null;
            if ($role === null && isset($user->role)) {
                $role = is_object($user->role) ? ($user->role->code ?? null) : $user->role;
            }
        }

        if ($role === null || !in_array($role, $roles, true)) {
            return ApiResponse::error('Bạn không có quyền thực hiện thao tác này', [], 403, 'FORBIDDEN');
        }

        return $next($request);
    }
}
