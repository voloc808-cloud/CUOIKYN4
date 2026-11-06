<?php
namespace App\Http\Controllers;
use App\Http\Requests\AlertSettingRequest;
use App\Services\AlertService;
use Illuminate\Http\Request;
use Pims\Common\Http\ApiResponse;
final class AlertController extends Controller
{
    public function __construct(private readonly AlertService $service) {}
    public function index(Request $request) { return ApiResponse::success($this->service->alerts((string) $request->bearerToken())); }
    public function update(Request $request, AlertSettingRequest $validated) { return ApiResponse::success($this->service->update($validated->validate($request)), 'Cập nhật cấu hình cảnh báo thành công'); }
}
