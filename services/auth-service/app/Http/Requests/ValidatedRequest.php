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
        $validator = $this->validator->make(
            $request->all(),
            $this->rules($context),
            $this->messages()
        );
        $validator->setAttributeNames([
            'name' => 'Họ tên',
            'email' => 'Email',
            'role' => 'Vai trò',
            'password' => 'Mật khẩu',
            'current_password' => 'Mật khẩu hiện tại',
            'is_active' => 'Trạng thái',
        ]);

        return $validator->validate();
    }

    abstract protected function rules(array $context): array;

    protected function messages(): array
    {
        return [
            'required' => ':attribute là bắt buộc.',
            'email' => ':attribute không đúng định dạng.',
            'unique' => ':attribute đã được sử dụng.',
            'min' => ':attribute phải có ít nhất :min ký tự.',
            'confirmed' => 'Xác nhận mật khẩu không khớp.',
            'in' => ':attribute không hợp lệ.',
            'boolean' => ':attribute phải là true hoặc false.',
        ];
    }
}
