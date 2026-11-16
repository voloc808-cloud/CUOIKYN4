<?php

namespace App\Http\Controllers;

use App\Clients\AuthClient;
use App\Exceptions\ServiceApiException;
use Illuminate\Http\Request;
use Pims\Common\Exceptions\ServiceUnavailableException;

final class UserController extends Controller
{
    public function __construct(private readonly AuthClient $auth)
    {
    }

    public function index(Request $request)
    {
        try {
            $users = $this->auth->users(
                (string) $request->session()->get('pims_token'),
                (string) $request->query('search', ''),
                max(1, (int) $request->query('page', 1))
            );
        } catch (ServiceApiException|ServiceUnavailableException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.form', ['user' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        return $this->perform(
            fn () => $this->auth->createUser($this->token($request), $data),
            'Tạo tài khoản thành công.',
            route('users.index')
        );
    }

    public function edit(Request $request, int $id)
    {
        try {
            $user = $this->auth->user($this->token($request), $id);
        } catch (ServiceApiException|ServiceUnavailableException $exception) {
            return redirect()->route('users.index')->with('error', $exception->getMessage());
        }

        return view('users.form', compact('user'));
    }

    public function update(Request $request, int $id)
    {
        $data = $this->validated($request, false);
        return $this->perform(
            fn () => $this->auth->updateUser($this->token($request), $id, $data),
            'Cập nhật tài khoản thành công.',
            route('users.index')
        );
    }

    public function status(Request $request, int $id)
    {
        return $this->perform(
            fn () => $this->auth->updateStatus($this->token($request), $id, $request->boolean('is_active')),
            'Cập nhật trạng thái thành công.',
            route('users.index')
        );
    }

    public function destroy(Request $request, int $id)
    {
        return $this->perform(
            fn () => $this->auth->deleteUser($this->token($request), $id),
            'Xóa tài khoản thành công.',
            route('users.index')
        );
    }

    private function validated(Request $request, bool $creating): array
    {
        return $request->validate([
            'name' => ['required', 'max:150'],
            'email' => ['required', 'email', 'max:180'],
            'role' => ['required', 'in:DOCTOR,PHARMACIST'],
            'password' => [$creating ? 'required' : 'nullable', 'min:8', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'role.required' => 'Vui lòng chọn vai trò.',
            'role.in' => 'Vai trò không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);
    }

    private function token(Request $request): string
    {
        return (string) $request->session()->get('pims_token');
    }

    private function perform(callable $action, string $message, string $redirect)
    {
        try {
            $action();
        } catch (ServiceApiException|ServiceUnavailableException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect($redirect)->with('success', $message);
    }
}
