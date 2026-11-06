<?php

namespace App\Http\Controllers;

use App\Clients\CatalogClient;
use App\Clients\InventoryClient;
use App\Exceptions\ServiceApiException;
use Illuminate\Http\Request;
use Pims\Common\Exceptions\ServiceUnavailableException;

final class MedicineController extends Controller
{
    public function __construct(
        private readonly CatalogClient $catalog,
        private readonly InventoryClient $inventory
    ) {}

    public function index(Request $request)
    {
        try {
            $medicines = $this->catalog->medicines($this->token($request), (string) $request->query('search', ''), max(1, (int) $request->query('page', 1)));
        } catch (ServiceApiException|ServiceUnavailableException $exception) {
            return back()->with('error', $exception->getMessage());
        }
        return view('medicines.index', compact('medicines'));
    }

    public function create(Request $request)
    {
        return $this->form($request);
    }

    public function edit(Request $request, int $id)
    {
        return $this->form($request, $id);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        return $this->perform(fn () => $this->catalog->createMedicine($this->token($request), $data), 'Tạo thuốc thành công.');
    }

    public function update(Request $request, int $id)
    {
        $data = $this->validated($request);
        return $this->perform(fn () => $this->catalog->updateMedicine($this->token($request), $id, $data), 'Cập nhật thuốc thành công.');
    }

    public function destroy(Request $request, int $id)
    {
        try {
            $token = $this->token($request);
            if ($this->inventory->medicineHasBatches($token, $id)) {
                return back()->with('error', 'Không thể xóa thuốc đã có lô trong kho.');
            }
            $this->catalog->deleteMedicine($token, $id);
        } catch (ServiceApiException|ServiceUnavailableException $exception) {
            return back()->with('error', $exception->getMessage());
        }
        return redirect()->route('medicines.index')->with('success', 'Xóa thuốc thành công.');
    }

    private function form(Request $request, ?int $id = null)
    {
        try {
            $units = $this->catalog->units($this->token($request));
            $medicine = $id === null ? null : $this->catalog->medicine($this->token($request), $id);
        } catch (ServiceApiException|ServiceUnavailableException $exception) {
            return redirect()->route('medicines.index')->with('error', $exception->getMessage());
        }
        return view('medicines.form', compact('medicine', 'units'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'max:50'], 'name' => ['required', 'max:180'],
            'active_ingredient' => ['required', 'max:250'], 'strength' => ['required', 'max:100'],
            'dosage_form' => ['nullable', 'max:120'], 'base_unit_id' => ['required', 'integer'],
            'minimum_stock' => ['required', 'numeric', 'min:0'], 'is_active' => ['required', 'boolean'],
        ]);
    }

    private function token(Request $request): string
    {
        return (string) $request->session()->get('pims_token');
    }

    private function perform(callable $action, string $message)
    {
        try { $action(); } catch (ServiceApiException|ServiceUnavailableException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
        return redirect()->route('medicines.index')->with('success', $message);
    }
}
