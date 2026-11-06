<?php
namespace App\Http\Controllers;
use App\Services\ReportDataService;
use Illuminate\Http\Request;
use Pims\Common\Http\ApiResponse;
final class ReportDataController extends Controller
{
    public function __construct(private readonly ReportDataService $service) {}
    public function prescriptions(Request $request) { $data = validator($request->all(), ['from' => ['required','date'], 'to' => ['required','date','after_or_equal:from']])->validate(); return ApiResponse::success($this->service->prescriptions($data['from'], $data['to'])); }
}
