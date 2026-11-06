<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\Factory;

final class RestoreStockRequest
{
    public function __construct(private readonly Factory $validator) {}

    public function validate(Request $request): array
    {
        return $this->validator->make($request->all(), [
            'prescription_id' => ['required', 'integer', 'min:1'],
        ])->validate();
    }
}
