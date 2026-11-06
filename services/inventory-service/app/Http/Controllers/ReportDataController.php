<?php
namespace App\Http\Controllers;
use App\Services\ReportDataService;
use Illuminate\Http\Request;
use Pims\Common\Http\ApiResponse;
final class ReportDataController extends Controller
{
    public function __construct(private readonly ReportDataService $service) {}
    public function stock(Request $request) { $data = $this->validateDates($request); return ApiResponse::success($this->service->stock($data['from'], $data['to'])); }
    private function validateDates(Request $request): array { return validator($request->all(), ['from' => ['required','date'], 'to' => ['required','date','after_or_equal:from']])->validate(); }
}
