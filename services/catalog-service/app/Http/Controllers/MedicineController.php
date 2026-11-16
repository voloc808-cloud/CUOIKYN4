<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicineRequest;
use App\Services\MedicineService;
use Illuminate\Http\Request;
use Pims\Common\Http\ApiResponse;

final class MedicineController extends Controller
{
    public function __construct(private readonly MedicineService $medicines)
    {
    }

    public function index(Request $request)
    {
        return ApiResponse::success($this->medicines->paginate((string) $request->query('search', '')));
    }

    public function show(int $id)
    {
        return ApiResponse::success($this->medicines->find($id));
    }

    public function stockThresholds()
    {
        return ApiResponse::success($this->medicines->stockThresholds());
    }

    public function store(Request $request, MedicineRequest $validated)
    {
        return ApiResponse::success($this->medicines->create($validated->validate($request)), 'Tạo thuốc thành công', 201);
    }

    public function update(int $id, Request $request, MedicineRequest $validated)
    {
        return ApiResponse::success($this->medicines->update($id, $validated->validate($request, ['id' => $id])), 'Cập nhật thuốc thành công');
    }

    public function destroy(int $id)
    {
        $this->medicines->delete($id);
        return ApiResponse::success(null, 'Xóa thuốc thành công');
    }

    public function destroyVerified(int $id)
    {
        $this->medicines->deleteVerified($id);
        return ApiResponse::success(null, 'Xóa thuốc thành công');
    }
}
