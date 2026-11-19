<?php

namespace App\Http\Controllers;

use App\Clients\AuthClient;
use App\Exceptions\ServiceApiException;
use Illuminate\Http\Request;
use Pims\Common\Exceptions\ServiceUnavailableException;

final class AuthController extends Controller
{
    public function __construct(private readonly AuthClient $auth)
    {
    }

    public function create(Request $request)
    {
        if ($request->session()->has('pims_token')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        try {
            $result = $this->auth->login($credentials['email'], $credentials['password']);
        } catch (ServiceApiException|ServiceUnavailableException $exception) {
            return back()->withInput($request->only('email'))->with('error', $exception->getMessage());
        }

        $request->session()->regenerate();
        $request->session()->put('pims_token', $result['access_token']);
        $request->session()->put('pims_user', $result['user']);

        return redirect()->route('dashboard')->with('success', 'Đăng nhập thành công.');
    }

    public function destroy(Request $request)
    {
        try {
            $this->auth->logout((string) $request->session()->get('pims_token'));
        } catch (ServiceApiException|ServiceUnavailableException) {
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Đã đăng xuất.');
    }
}
