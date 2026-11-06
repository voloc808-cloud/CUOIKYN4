<?php

namespace App\Http\Requests;

final class LoginRequest extends ValidatedRequest
{
    protected function rules(array $context): array
    {
        return ['email' => ['required', 'email'], 'password' => ['required', 'string']];
    }
}
