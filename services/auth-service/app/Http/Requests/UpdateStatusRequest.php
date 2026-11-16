<?php

namespace App\Http\Requests;

final class UpdateStatusRequest extends ValidatedRequest
{
    protected function rules(array $context): array
    {
        return ['is_active' => ['required', 'boolean']];
    }
}
