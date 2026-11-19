<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class PimsRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $role = $request->session()->get('pims_user.role_code');
        if (!in_array($role, $roles, true)) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        return $next($request);
    }
}
