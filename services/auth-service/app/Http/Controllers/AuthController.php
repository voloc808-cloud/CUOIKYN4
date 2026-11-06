<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Pims\Common\Http\ApiResponse;

final class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function login(Request $request, LoginRequest $validated)
    {
        $data = $validated->validate($request);
        return ApiResponse::success($this->auth->login($data['email'], $data['password']), 'Đăng nhập thành công');
    }

    public function logout()
    {
        $this->auth->logout();
        return ApiResponse::success(null, 'Đăng xuất thành công');
    }

    public function changePassword(Request $request, ChangePasswordRequest $validated)
    {
        $data = $validated->validate($request);
        /** @var object{id:int} $user */
        $user = $request->attributes->get('auth_user');
        $this->auth->changePassword($user->id, $data['current_password'], $data['password']);

        return ApiResponse::success(null, 'Đổi mật khẩu thành công');
    }
}
