<?php

declare(strict_types=1);

namespace Pims\Common\Http;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success(mixed $data = null, string $message = '', int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => $message,
            'errors' => (object) [],
        ], $status);
    }

    public static function error(
        string $message,
        array $errors = [],
        int $status = 400,
        ?string $code = null
    ): JsonResponse {
        $payload = [
            'status' => 'error',
            'data' => null,
            'message' => $message,
            'errors' => $errors ?: (object) [],
        ];

        if ($code !== null) {
            $payload['code'] = $code;
        }

        return response()->json($payload, $status);
    }
}
