<?php
namespace App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
final class ReportRequest
{
    public function __construct(private readonly Factory $validator) {}
    public function validate(Request $request): array { return $this->validator->make($request->all(), ['from' => ['required','date'], 'to' => ['required','date','after_or_equal:from']])->validate(); }
}
