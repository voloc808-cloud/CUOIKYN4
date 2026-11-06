<?php

namespace App\Http\Requests;

final class StoreUserRequest extends ValidatedRequest
{
    protected function rules(array $context): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'role' => ['required', 'in:DOCTOR,PHARMACIST'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
