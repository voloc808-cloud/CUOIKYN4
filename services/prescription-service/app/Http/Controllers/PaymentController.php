<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Models\Prescription;
use App\Models\PrescriptionPayment;
use App\Services\InsuranceCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Pims\Common\Http\ApiResponse;

final class PaymentController extends Controller
{
    public function __construct(private readonly InsuranceCalculator $calculator) {}

    public function store(int $id, Request $request)
    {
        $data = $this->validated($request);
        $payment = DB::transaction(function () use ($id, $data) {
            $prescription = Prescription::with('details')->lockForUpdate()->findOrFail($id);
            if ($prescription->status !== Prescription::NEW) {
                throw new BusinessException('Đơn thuốc đã được xử lý', 'INVALID_STATE_TRANSITION', 409);
            }
            $expected = $prescription->details->mapWithKeys(fn ($detail) => [(int) $detail->medicine_id => (float) $detail->quantity])->all();
            $received = [];
            foreach ($data['items'] as $item) {
                $medicineId = (int) $item['medicine_id'];
                if (isset($received[$medicineId]) || !isset($expected[$medicineId]) || abs($expected[$medicineId] - (float) $item['quantity']) > 0.0001 || $item['insurance_unit_price'] > $item['unit_price']) {
                    throw new BusinessException('Danh sách thuốc hoặc giá thanh toán không hợp lệ', 'INVALID_PAYMENT_ITEMS', 422);
                }
                $received[$medicineId] = true;
            }
            if (count($received) !== count($expected)) {
                throw new BusinessException('Thiếu thuốc trong bảng thanh toán', 'INVALID_PAYMENT_ITEMS', 422);
            }
            $quote = $this->calculator->calculate((int) $data['benefit_percent'], $data['items']);
            if ($data['method'] === 'QR' && $quote['patient_amount'] === 0) {
                throw new BusinessException('Không cần tạo QR khi bệnh nhân không phải trả tiền', 'NO_PAYMENT_DUE', 422);
            }
            $existing = PrescriptionPayment::where('prescription_id', $id)->first();
            if ($existing && $existing->status === 'PAID') {
                throw new BusinessException('Đơn đã xác nhận thanh toán', 'PAYMENT_ALREADY_CONFIRMED', 409);
            }
            return PrescriptionPayment::updateOrCreate(['prescription_id' => $id], [
                'benefit_percent' => $quote['benefit_percent'], 'items' => $quote['items'],
                'total' => $quote['total'], 'insurance_amount' => $quote['insurance_amount'],
                'patient_amount' => $quote['patient_amount'], 'method' => $data['method'],
                'status' => 'PENDING', 'confirmed_by' => null, 'confirmed_at' => null,
            ]);
        });
        return ApiResponse::success($payment, 'Đã lập bảng thanh toán', 201);
    }

    public function confirm(int $id, Request $request)
    {
        $payment = DB::transaction(function () use ($id, $request) {
            $prescription = Prescription::lockForUpdate()->findOrFail($id);
            if ($prescription->status !== Prescription::NEW) {
                throw new BusinessException('Đơn thuốc đã được xử lý', 'INVALID_STATE_TRANSITION', 409);
            }
            $payment = PrescriptionPayment::where('prescription_id', $id)->lockForUpdate()->firstOrFail();
            if ($payment->status !== 'PAID') {
                $payment->update(['status' => 'PAID', 'confirmed_by' => (int) $request->attributes->get('auth_user')->id, 'confirmed_at' => Carbon::now()]);
            }
            return $payment;
        });
        return ApiResponse::success($payment, 'Đã xác nhận nhận tiền');
    }

    private function validated(Request $request): array
    {
        return validator($request->all(), [
            'benefit_percent' => ['required', 'integer', 'in:0,80,95,100'],
            'method' => ['required', 'in:CASH,QR'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
            'items.*.insurance_unit_price' => ['required', 'integer', 'min:0'],
            'items.*.insurance_percent' => ['required', 'integer', 'between:0,100'],
        ])->validate();
    }
}
