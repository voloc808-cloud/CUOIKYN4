<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnitConversionRequest;
use App\Http\Requests\UnitRequest;
use App\Services\UnitService;
use Illuminate\Http\Request;
use Pims\Common\Http\ApiResponse;

final class UnitController extends Controller
{
    public function __construct(private readonly UnitService $units)
    {
    }

    public function index()
    {
        return ApiResponse::success($this->units->all());
    }

    public function store(Request $request, UnitRequest $validated)
    {
        return ApiResponse::success($this->units->create($validated->validate($request)), 'Tạo đơn vị thành công', 201);
    }

    public function conversions()
    {
        return ApiResponse::success($this->units->conversions());
    }

    public function storeConversion(Request $request, UnitConversionRequest $validated)
    {
        return ApiResponse::success($this->units->createConversion($validated->validate($request)), 'Tạo quy đổi thành công', 201);
    }
}
