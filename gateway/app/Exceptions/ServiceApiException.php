<?php

namespace App\Exceptions;

use RuntimeException;

final class ServiceApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly array $errors = [],
        public readonly int $statusCode = 422
    ) {
        parent::__construct($message);
    }
}
