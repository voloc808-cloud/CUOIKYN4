<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

final class SupplierRequest extends ValidatedRequest
{
    protected function rules(array $context): array
    {
        return [
            'code' => ['required', 'string', 'max:40', Rule::unique('suppliers', 'code')->ignore($context['id'] ?? null)],
            'name' => ['required', 'string', 'max:180'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:180'],
            'tax_code' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function attributes(): array
    {
        return ['code' => 'Mã nhà cung cấp', 'name' => 'Tên nhà cung cấp', 'email' => 'Email'];
    }
}
