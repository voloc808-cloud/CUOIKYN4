<?php

namespace App\Http\Controllers;

use App\Http\Requests\RestoreStockRequest;
use App\Http\Requests\StockRequest;
use App\Services\StockService;
use Illuminate\Http\Request;
use Pims\Common\Http\ApiResponse;

final class StockController extends Controller
{
    public function __construct(private readonly StockService $stock) {}

    public function check(Request $request, StockRequest $validated)
    {
        return ApiResponse::success($this->stock->check($validated->validate($request)));
    }

    public function status(int $prescriptionId)
    {
        return ApiResponse::success($this->stock->deductionStatus($prescriptionId));
    }

    public function deduct(Request $request, StockRequest $validated)
    {
        $actor = $request->attributes->get('auth_user');
        return ApiResponse::success($this->stock->deductFefo($validated->validate($request, true), (int) $actor->id));
    }

    public function restore(Request $request, RestoreStockRequest $validated)
    {
        $actor = $request->attributes->get('auth_user');
        return ApiResponse::success($this->stock->restore($validated->validate($request), (int) $actor->id));
    }
}
