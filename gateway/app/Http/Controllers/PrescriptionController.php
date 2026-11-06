<?php
namespace App\Http\Controllers;
use App\Clients\CatalogClient;
use App\Clients\PrescriptionClient;
use App\Exceptions\ServiceApiException;
use Illuminate\Http\Request;
use Pims\Common\Exceptions\ServiceUnavailableException;
final class PrescriptionController extends Controller
{
    public function __construct(private readonly PrescriptionClient $prescriptions, private readonly CatalogClient $catalog) {}
    public function index(Request $request) { try { $status = request()->routeIs('prescriptions.pending') ? 'NEW' : (string) $request->query('status', ''); return view('prescriptions.index', ['prescriptions' => $this->prescriptions->list($this->token($request), $status, max(1, (int) $request->query('page', 1)))]); } catch (ServiceApiException|ServiceUnavailableException $e) { return back()->with('error', $e->getMessage()); } }
    public function create(Request $request) { try { return view('prescriptions.create', ['medicines' => $this->catalog->medicines($this->token($request))['data'] ?? []]); } catch (ServiceApiException|ServiceUnavailableException $e) { return back()->with('error', $e->getMessage()); } }
    public function store(Request $request)
    {
        $data = $request->validate(['patient_name' => ['required','max:150'], 'patient_code' => ['nullable','max:50'], 'diagnosis' => ['nullable','max:1000'], 'items' => ['required','array','min:1'], 'items.*.medicine_id' => ['required','integer','distinct'], 'items.*.quantity' => ['required','numeric','gt:0'], 'items.*.instructions' => ['required','max:500']]);
        try { $item = $this->prescriptions->create($this->token($request), $data); return redirect()->route('prescriptions.show', $item['id'])->with('success', 'Kê đơn thành công.'); } catch (ServiceApiException|ServiceUnavailableException $e) { return back()->withInput()->with('error', $e->getMessage()); }
    }
    public function show(Request $request, int $id) { try { return view('prescriptions.show', ['prescription' => $this->prescriptions->show($this->token($request), $id)]); } catch (ServiceApiException|ServiceUnavailableException $e) { return back()->with('error', $e->getMessage()); } }
    public function approve(Request $request, int $id) { return $this->action($request, fn ($token) => $this->prescriptions->approve($token, $id), $id, 'Đã duyệt và cấp phát thuốc.'); }
    public function reject(Request $request, int $id) { $data = $request->validate(['reason' => ['required','max:1000']]); return $this->action($request, fn ($token) => $this->prescriptions->reject($token, $id, $data['reason']), $id, 'Đã từ chối đơn thuốc.'); }
    private function action(Request $request, callable $callback, int $id, string $message) { try { $callback($this->token($request)); return redirect()->route('prescriptions.show', $id)->with('success', $message); } catch (ServiceApiException|ServiceUnavailableException $e) { return back()->with('error', $e->getMessage()); } }
    private function token(Request $request): string { return (string) $request->session()->get('pims_token'); }
}
