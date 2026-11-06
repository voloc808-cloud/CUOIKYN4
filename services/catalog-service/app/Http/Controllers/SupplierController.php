<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Pims\Common\Http\ApiResponse;

final class SupplierController extends Controller
{
    public function __construct(private readonly SupplierService $suppliers)
    {
    }

    public function index(Request $request)
    {
        return ApiResponse::success($this->suppliers->paginate((string) $request->query('search', '')));
    }

    public function show(int $id)
    {
        return ApiResponse::success($this->suppliers->find($id));
    }

    public function store(Request $request, SupplierRequest $validated)
    {
        return ApiResponse::success($this->suppliers->create($validated->validate($request)), 'Tạo nhà cung cấp thành công', 201);
    }

    public function update(int $id, Request $request, SupplierRequest $validated)
    {
        return ApiResponse::success($this->suppliers->update($id, $validated->validate($request, ['id' => $id])), 'Cập nhật nhà cung cấp thành công');
    }

    public function destroy(int $id)
    {
        $this->suppliers->delete($id);
        return ApiResponse::success(null, 'Xóa nhà cung cấp thành công');
    }
}
