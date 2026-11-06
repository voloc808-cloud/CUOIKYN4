<?php
namespace App\Http\Controllers;
use App\Clients\ReportClient;
use App\Exceptions\ServiceApiException;
use Illuminate\Http\Request;
use Pims\Common\Exceptions\ServiceUnavailableException;
final class ReportController extends Controller
{
    public function __construct(private readonly ReportClient $reports) {}
    public function index(Request $request)
    {
        [$from, $to] = $this->dates($request);
        try { return view('reports.stock', ['report' => $this->reports->stock($this->token($request), $from, $to), 'from' => $from, 'to' => $to]); }
        catch (ServiceApiException|ServiceUnavailableException $exception) { return back()->with('error', $exception->getMessage()); }
    }
    public function download(Request $request, string $format)
    {
        abort_unless(in_array($format, ['xlsx','pdf'], true), 404); [$from, $to] = $this->dates($request);
        try { $file = $this->reports->download($this->token($request), $format, $from, $to); return response($file['content'], 200, ['Content-Type' => $file['type'], 'Content-Disposition' => "attachment; filename=bao-cao-{$from}-{$to}.{$format}"]); }
        catch (ServiceApiException|ServiceUnavailableException $exception) { return back()->with('error', $exception->getMessage()); }
    }
    private function dates(Request $request): array { $data = $request->validate(['from' => ['nullable','date'], 'to' => ['nullable','date','after_or_equal:from']]); return [$data['from'] ?? now()->startOfMonth()->toDateString(), $data['to'] ?? now()->toDateString()]; }
    private function token(Request $request): string { return (string) $request->session()->get('pims_token'); }
}
