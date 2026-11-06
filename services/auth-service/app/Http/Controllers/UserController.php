<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Pims\Common\Http\ApiResponse;

final class UserController extends Controller
{
    public function __construct(private readonly AccountService $accounts)
    {
    }

    public function index(Request $request)
    {
        return ApiResponse::success($this->accounts->paginate((string) $request->query('search', '')));
    }

    public function show(int $id)
    {
        return ApiResponse::success($this->accounts->find($id));
    }

    public function store(Request $request, StoreUserRequest $validated)
    {
        return ApiResponse::success($this->accounts->create($validated->validate($request)), 'Tạo tài khoản thành công', 201);
    }

    public function update(int $id, Request $request, UpdateUserRequest $validated)
    {
        return ApiResponse::success($this->accounts->update($id, $validated->validate($request, ['id' => $id])), 'Cập nhật tài khoản thành công');
    }

    public function status(int $id, Request $request, UpdateStatusRequest $validated)
    {
        $data = $validated->validate($request);
        /** @var object{id:int} $actor */
        $actor = $request->attributes->get('auth_user');

        return ApiResponse::success($this->accounts->changeStatus($id, $data['is_active'], $actor->id), 'Cập nhật trạng thái thành công');
    }

    public function destroy(int $id, Request $request)
    {
        /** @var object{id:int} $actor */
        $actor = $request->attributes->get('auth_user');
        $this->accounts->delete($id, $actor->id);

        return ApiResponse::success(null, 'Xóa tài khoản thành công');
    }
}
