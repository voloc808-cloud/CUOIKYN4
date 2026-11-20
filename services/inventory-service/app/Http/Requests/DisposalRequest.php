<?php
namespace App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
final class DisposalRequest
{
    public function __construct(private readonly Factory $validator) {}
    public function validate(Request $request): array {
        return $this->validator->make($request->all(), [
            'receipt_code' => ['required','string','max:50','unique:disposal_receipts,receipt_code'],
            'disposed_at' => ['required','date','before_or_equal:now'], 'reason' => ['required','string','max:1000'],
            'items' => ['required','array','min:1'], 'items.*.batch_id' => ['required','integer','distinct','min:1'],
            'items.*.quantity' => ['required','numeric','gt:0'],
        ], ['disposed_at.before_or_equal' => 'Thời gian xuất hủy không được lớn hơn thời điểm hiện tại.', 'reason.required' => 'Lý do xuất hủy là bắt buộc.', 'items.*.quantity.gt' => 'Số lượng xuất hủy phải lớn hơn 0.'])->validate();
    }
}
