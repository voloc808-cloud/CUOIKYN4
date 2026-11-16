<?php
namespace App\Http\Controllers;
use App\Http\Requests\DisposalRequest;
use App\Services\DisposalService;
use Illuminate\Http\Request;
use Pims\Common\Http\ApiResponse;
final class DisposalController extends Controller
{
    public function __construct(private readonly DisposalService $service) {}
    public function index() { return ApiResponse::success($this->service->paginate()); }
    public function show(int $id) { return ApiResponse::success($this->service->find($id)); }
    public function store(Request $request, DisposalRequest $validated) { $actor = $request->attributes->get('auth_user'); return ApiResponse::success($this->service->create($validated->validate($request), (int) $actor->id), 'Xuất hủy thành công', 201); }
}
