<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class PimsAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('pims_token') || !$request->session()->has('pims_user')) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        view()->share('currentUser', $request->session()->get('pims_user'));
        return $next($request);
    }
}
