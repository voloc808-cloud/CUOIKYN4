<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\Factory;

final class ImportReceiptRequest
{
    public function __construct(private readonly Factory $validator) {}

    public function validate(Request $request): array
    {
        $validator = $this->validator->make($request->all(), [
            'receipt_code' => ['required', 'string', 'max:50', 'unique:import_receipts,receipt_code'],
            'supplier_id' => ['required', 'integer', 'min:1'],
            'imported_at' => ['required', 'date'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'integer', 'min:1'],
            'items.*.unit_id' => ['required', 'integer', 'min:1'],
            'items.*.batch_code' => ['required', 'string', 'max:80'],
            'items.*.manufactured_at' => ['required', 'date'],
            'items.*.expires_at' => ['required', 'date', 'after:items.*.manufactured_at'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ], [
            'required' => ':attribute là bắt buộc.',
            'unique' => ':attribute đã tồn tại.',
            'items.min' => 'Phiếu nhập phải có ít nhất một lô.',
            'after' => 'Hạn sử dụng phải sau ngày sản xuất.',
            'gt' => 'Số lượng nhập phải lớn hơn 0.',
        ]);
        $validator->setAttributeNames(['receipt_code' => 'Mã phiếu', 'supplier_id' => 'Nhà cung cấp', 'imported_at' => 'Ngày nhập', 'items' => 'Danh sách lô']);
        return $validator->validate();
    }
}
