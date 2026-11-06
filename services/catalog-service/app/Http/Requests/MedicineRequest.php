<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

final class MedicineRequest extends ValidatedRequest
{
    protected function rules(array $context): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('medicines', 'code')->ignore($context['id'] ?? null)],
            'name' => ['required', 'string', 'max:180'],
            'active_ingredient' => ['required', 'string', 'max:250'],
            'strength' => ['required', 'string', 'max:100'],
            'dosage_form' => ['nullable', 'string', 'max:120'],
            'base_unit_id' => ['required', 'integer', 'exists:units,id'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function attributes(): array
    {
        return ['code' => 'Mã thuốc', 'name' => 'Tên thuốc', 'active_ingredient' => 'Hoạt chất', 'strength' => 'Hàm lượng', 'base_unit_id' => 'Đơn vị cơ sở', 'minimum_stock' => 'Ngưỡng tồn tối thiểu'];
    }
}
