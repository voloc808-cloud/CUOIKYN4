<?php
namespace App\Http\Controllers;
use App\Http\Requests\ReportRequest;
use App\Services\ReportExportService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Pims\Common\Http\ApiResponse;
final class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports, private readonly ReportExportService $export) {}
    public function index(Request $request, ReportRequest $validated) { $data = $validated->validate($request); return ApiResponse::success($this->reports->aggregate($data['from'], $data['to'], (string) $request->bearerToken())); }
    public function excel(Request $request, ReportRequest $validated) { $data = $validated->validate($request); $content = $this->export->excel($this->reports->aggregate($data['from'], $data['to'], (string) $request->bearerToken())); return response($content, 200, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Content-Disposition' => 'attachment; filename="bao-cao-nhap-xuat-ton.xlsx"']); }
    public function pdf(Request $request, ReportRequest $validated) { $data = $validated->validate($request); $content = $this->export->pdf($this->reports->aggregate($data['from'], $data['to'], (string) $request->bearerToken())); return response($content, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="bao-cao-nhap-xuat-ton.pdf"']); }
}
