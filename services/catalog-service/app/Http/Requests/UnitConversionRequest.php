<?php

namespace App\Http\Requests;

final class UnitConversionRequest extends ValidatedRequest
{
    protected function rules(array $context): array
    {
        return [
            'medicine_id' => ['required', 'integer', 'exists:medicines,id'],
            'from_unit_id' => ['required', 'integer', 'exists:units,id', 'different:to_unit_id'],
            'to_unit_id' => ['required', 'integer', 'exists:units,id'],
            'factor' => ['required', 'numeric', 'min:0.000001'],
        ];
    }

    protected function attributes(): array
    {
        return ['medicine_id' => 'Thuốc', 'from_unit_id' => 'Đơn vị nguồn', 'to_unit_id' => 'Đơn vị đích', 'factor' => 'Hệ số quy đổi'];
    }
}
