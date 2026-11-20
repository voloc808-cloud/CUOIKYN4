<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\Factory;

final class StockRequest
{
    public function __construct(private readonly Factory $validator) {}

    public function validate(Request $request, bool $requirePrescription = false): array
    {
        return $this->validator->make($request->all(), [
            'prescription_id' => [$requirePrescription ? 'required' : 'nullable', 'integer', 'min:1'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ])->validate();
    }
}
