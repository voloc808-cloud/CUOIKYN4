<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportReceiptRequest;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Pims\Common\Http\ApiResponse;

final class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function receipts(Request $request)
    {
        return ApiResponse::success($this->inventory->receipts((string) $request->query('search', ''), (string) $request->bearerToken()));
    }

    public function receipt(int $id, Request $request)
    {
        return ApiResponse::success($this->inventory->receipt($id, (string) $request->bearerToken()));
    }

    public function store(Request $request, ImportReceiptRequest $validated)
    {
        $actor = $request->attributes->get('auth_user');
        return ApiResponse::success(
            $this->inventory->import($validated->validate($request), $actor->id, (string) $request->bearerToken()),
            'Nhập kho thành công',
            201
        );
    }

    public function batches(Request $request)
    {
        return ApiResponse::success($this->inventory->batches((string) $request->query('search', '')));
    }

    public function movements()
    {
        return ApiResponse::success($this->inventory->movements());
    }

    public function hasBatches(int $medicineId)
    {
        return ApiResponse::success(['has_batches' => $this->inventory->hasBatches($medicineId)]);
    }
}
