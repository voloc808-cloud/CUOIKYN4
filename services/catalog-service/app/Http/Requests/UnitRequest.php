<?php

namespace App\Http\Requests;

final class UnitRequest extends ValidatedRequest
{
    protected function rules(array $context): array
    {
        return ['code' => ['required', 'string', 'max:30', 'unique:units,code'], 'name' => ['required', 'string', 'max:100']];
    }

    protected function attributes(): array
    {
        return ['code' => 'Mã đơn vị', 'name' => 'Tên đơn vị'];
    }
}
