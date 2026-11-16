<?php
namespace App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
final class PrescriptionRequest
{
    public function __construct(private readonly Factory $validator) {}
    public function validate(Request $request): array
    {
        return $this->validator->make($request->all(), [
            'patient_name' => ['required', 'string', 'max:150'], 'patient_code' => ['nullable', 'string', 'max:50'],
            'diagnosis' => ['nullable', 'string', 'max:1000'], 'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'integer', 'distinct', 'min:1'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'], 'items.*.instructions' => ['required', 'string', 'max:500'],
        ])->validate();
    }
}
