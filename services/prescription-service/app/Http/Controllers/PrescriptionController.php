<?php
namespace App\Http\Controllers;
use App\Http\Requests\PrescriptionRequest;
use App\Http\Requests\RejectRequest;
use App\Services\PrescriptionService;
use Illuminate\Http\Request;
use Pims\Common\Http\ApiResponse;
final class PrescriptionController extends Controller
{
    public function __construct(private readonly PrescriptionService $service) {}
    public function index(Request $request)
    {
        $user = $request->attributes->get('auth_user');
        return ApiResponse::success($this->service->list($user->role_code, (int) $user->id, (string) $request->query('status', '')));
    }
    public function show(int $id, Request $request)
    {
        $user = $request->attributes->get('auth_user');
        return ApiResponse::success($this->service->show($id, $user->role_code, (int) $user->id, (string) $request->bearerToken()));
    }
    public function store(Request $request, PrescriptionRequest $validated)
    {
        $user = $request->attributes->get('auth_user');
        return ApiResponse::success($this->service->create($validated->validate($request), (int) $user->id, (string) $request->bearerToken()), 'Kê đơn thành công', 201);
    }
    public function approve(int $id, Request $request)
    {
        $user = $request->attributes->get('auth_user');
        return ApiResponse::success($this->service->approve($id, (int) $user->id, (string) $request->bearerToken(), (string) $request->headers->get('X-Correlation-ID')), 'Đã duyệt và cấp phát thuốc');
    }
    public function reject(int $id, Request $request, RejectRequest $validated)
    {
        $user = $request->attributes->get('auth_user'); $data = $validated->validate($request);
        return ApiResponse::success($this->service->reject($id, $data['reason'], (int) $user->id), 'Đã từ chối đơn thuốc');
    }
}
