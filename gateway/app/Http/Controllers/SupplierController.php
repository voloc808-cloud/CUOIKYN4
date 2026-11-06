<?php

namespace App\Http\Controllers;

use App\Clients\CatalogClient;
use App\Exceptions\ServiceApiException;
use Illuminate\Http\Request;
use Pims\Common\Exceptions\ServiceUnavailableException;

final class SupplierController extends Controller
{
    public function __construct(private readonly CatalogClient $catalog) {}

    public function index(Request $request)
    {
        try {
            $suppliers = $this->catalog->suppliers($this->token($request), (string) $request->query('search', ''), max(1, (int) $request->query('page', 1)));
        } catch (ServiceApiException|ServiceUnavailableException $exception) {
            return back()->with('error', $exception->getMessage());
        }
        return view('suppliers.index', compact('suppliers'));
    }

    public function create() { return view('suppliers.form', ['supplier' => null]); }

    public function edit(Request $request, int $id)
    {
        try { $supplier = $this->catalog->supplier($this->token($request), $id); }
        catch (ServiceApiException|ServiceUnavailableException $exception) { return redirect()->route('suppliers.index')->with('error', $exception->getMessage()); }
        return view('suppliers.form', compact('supplier'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        return $this->perform(fn () => $this->catalog->createSupplier($this->token($request), $data), 'Tạo nhà cung cấp thành công.');
    }

    public function update(Request $request, int $id)
    {
        $data = $this->validated($request);
        return $this->perform(fn () => $this->catalog->updateSupplier($this->token($request), $id, $data), 'Cập nhật nhà cung cấp thành công.');
    }

    public function destroy(Request $request, int $id)
    {
        return $this->perform(fn () => $this->catalog->deleteSupplier($this->token($request), $id), 'Xóa nhà cung cấp thành công.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'max:40'], 'name' => ['required', 'max:180'],
            'contact_person' => ['nullable', 'max:150'], 'phone' => ['nullable', 'max:30'],
            'email' => ['nullable', 'email', 'max:180'], 'tax_code' => ['nullable', 'max:50'],
            'address' => ['nullable', 'max:500'], 'is_active' => ['required', 'boolean'],
        ]);
    }

    private function token(Request $request): string { return (string) $request->session()->get('pims_token'); }

    private function perform(callable $action, string $message)
    {
        try { $action(); } catch (ServiceApiException|ServiceUnavailableException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
        return redirect()->route('suppliers.index')->with('success', $message);
    }
}
