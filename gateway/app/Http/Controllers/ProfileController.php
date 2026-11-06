<?php

namespace App\Http\Controllers;

use App\Clients\AuthClient;
use App\Exceptions\ServiceApiException;
use Illuminate\Http\Request;
use Pims\Common\Exceptions\ServiceUnavailableException;

final class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.password');
    }

    public function update(Request $request, AuthClient $auth)
    {
        $data = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        try {
            $auth->changePassword((string) $request->session()->get('pims_token'), $data);
        } catch (ServiceApiException|ServiceUnavailableException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Đổi mật khẩu thành công.');
    }
}
