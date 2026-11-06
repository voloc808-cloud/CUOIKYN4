<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

final class UpdateUserRequest extends ValidatedRequest
{
    protected function rules(array $context): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($context['id'])],
            'role' => ['required', 'in:DOCTOR,PHARMACIST'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
