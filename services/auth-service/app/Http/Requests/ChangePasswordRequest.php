<?php

namespace App\Http\Requests;

final class ChangePasswordRequest extends ValidatedRequest
{
    protected function rules(array $context): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
