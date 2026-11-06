<?php
namespace App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
final class AlertSettingRequest
{
    public function __construct(private readonly Factory $validator) {}
    public function validate(Request $request): array { return $this->validator->make($request->all(), ['expiry_warning_days' => ['required','integer','min:1','max:365']])->validate(); }
}
