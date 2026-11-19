<?php

namespace App\Http\Controllers;

use App\Clients\CatalogClient;
use App\Clients\InventoryClient;
use App\Exceptions\ServiceApiException;
use Illuminate\Http\Request;
use Pims\Common\Exceptions\ServiceUnavailableException;

final class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryClient $inventory,
        private readonly CatalogClient $catalog
    ) {}

    public function receipts(Request $request)
    {
        return $this->viewData($request, 'inventory.receipts', fn ($token) => ['receipts' => $this->inventory->receipts($token, (string) $request->query('search', ''), max(1, (int) $request->query('page', 1)))]);
    }

    public function show(Request $request, int $id)
    {
        return $this->viewData($request, 'inventory.receipt-show', fn ($token) => ['receipt' => $this->inventory->receipt($token, $id)]);
    }

    public function create(Request $request)
    {
        return $this->viewData($request, 'inventory.import-form', function ($token): array {
            $first = $this->catalog->medicines($token, '', 1);
            $second = ($first['last_page'] ?? 1) > 1 ? $this->catalog->medicines($token, '', 2) : ['data' => []];
            return [
                'medicines' => array_merge($first['data'] ?? [], $second['data'] ?? []),
                'suppliers' => $this->catalog->suppliers($token)['data'] ?? [],
                'units' => $this->catalog->units($token),
            ];
        });
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'receipt_code' => ['required', 'max:50'], 'supplier_id' => ['required', 'integer'],
            'imported_at' => ['required', 'date'], 'invoice_number' => ['nullable', 'max:100'], 'note' => ['nullable', 'max:500'],
            'items' => ['required', 'array', 'min:1'], 'items.*.medicine_id' => ['required', 'integer'],
            'items.*.unit_id' => ['required', 'integer'], 'items.*.batch_code' => ['required', 'max:80'],
            'items.*.manufactured_at' => ['required', 'date'], 'items.*.expires_at' => ['required', 'date'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'], 'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ], ['items.min' => 'Phiếu nhập phải có ít nhất một lô.', 'items.*.quantity.gt' => 'Số lượng phải lớn hơn 0.']);
        try {
            $receipt = $this->inventory->createReceipt($this->token($request), $data);
        } catch (ServiceApiException|ServiceUnavailableException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
        return redirect()->route('inventory.receipts.show', $receipt['id'])->with('success', 'Nhập kho thành công.');
    }

    public function batches(Request $request)
    {
        return $this->viewData($request, 'inventory.batches', fn ($token) => ['batches' => $this->inventory->batches($token, (string) $request->query('search', ''), max(1, (int) $request->query('page', 1)))]);
    }

    public function movements(Request $request)
    {
        return $this->viewData($request, 'inventory.movements', fn ($token) => ['movements' => $this->inventory->movements($token, max(1, (int) $request->query('page', 1)))]);
    }

    public function disposals(Request $request)
    {
        return $this->viewData($request, 'inventory.disposals', fn ($token) => ['disposals' => $this->inventory->disposals($token, max(1, (int) $request->query('page', 1)))]);
    }

    public function createDisposal(Request $request)
    {
        return $this->viewData($request, 'inventory.disposal-form', fn ($token) => ['batches' => $this->inventory->batches($token)['data'] ?? []]);
    }

    public function storeDisposal(Request $request)
    {
        $data = $request->validate(['receipt_code' => ['required','max:50'], 'disposed_at' => ['required','date'], 'reason' => ['required','max:1000'], 'items' => ['required','array','min:1'], 'items.*.batch_id' => ['required','integer','distinct'], 'items.*.quantity' => ['required','numeric','gt:0']]);
        try { $receipt = $this->inventory->createDisposal($this->token($request), $data); return redirect()->route('inventory.disposals.show', $receipt['id'])->with('success', 'Xuất hủy thành công.'); }
        catch (ServiceApiException|ServiceUnavailableException $exception) { return back()->withInput()->with('error', $exception->getMessage()); }
    }

    public function showDisposal(Request $request, int $id)
    {
        return $this->viewData($request, 'inventory.disposal-show', fn ($token) => ['receipt' => $this->inventory->disposal($token, $id)]);
    }

    public function alerts(Request $request)
    {
        return $this->viewData($request, 'inventory.alerts', fn ($token) => ['alerts' => $this->inventory->alerts($token)]);
    }

    public function updateAlertSettings(Request $request)
    {
        $data = $request->validate(['expiry_warning_days' => ['required','integer','min:1','max:365']]);
        try { $this->inventory->updateAlertSettings($this->token($request), (int) $data['expiry_warning_days']); return back()->with('success', 'Đã cập nhật số ngày cảnh báo.'); }
        catch (ServiceApiException|ServiceUnavailableException $exception) { return back()->with('error', $exception->getMessage()); }
    }

    private function viewData(Request $request, string $view, callable $loader)
    {
        try { return view($view, $loader($this->token($request))); }
        catch (ServiceApiException|ServiceUnavailableException $exception) { return back()->with('error', $exception->getMessage()); }
    }

    private function token(Request $request): string { return (string) $request->session()->get('pims_token'); }
}
