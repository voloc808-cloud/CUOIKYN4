<?php

namespace App\Exceptions;

use RuntimeException;

final class BusinessException extends RuntimeException
{
    public function __construct(string $message, public readonly string $errorCode, public readonly int $statusCode = 422)
    {
        parent::__construct($message);
    }
}
