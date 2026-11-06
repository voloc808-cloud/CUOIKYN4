<?php
namespace App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
final class RejectRequest
{
    public function __construct(private readonly Factory $validator) {}
    public function validate(Request $request): array { return $this->validator->make($request->all(), ['reason' => ['required', 'string', 'max:1000']])->validate(); }
}
