<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\Factory;

abstract class ValidatedRequest
{
    public function __construct(protected readonly Factory $validator)
    {
    }

    public function validate(Request $request, array $context = []): array
    {
        $validator = $this->validator->make($request->all(), $this->rules($context), [
            'required' => ':attribute là bắt buộc.',
            'unique' => ':attribute đã tồn tại.',
            'numeric' => ':attribute phải là số.',
            'min' => ':attribute phải lớn hơn hoặc bằng :min.',
            'different' => ':attribute phải khác :other.',
            'email' => ':attribute không đúng định dạng.',
        ]);
        $validator->setAttributeNames($this->attributes());
        return $validator->validate();
    }

    abstract protected function rules(array $context): array;

    abstract protected function attributes(): array;
}
