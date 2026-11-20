<?php

declare(strict_types=1);

namespace Pims\Common\Exceptions;

use RuntimeException;

final class ServiceUnavailableException extends RuntimeException
{
    public function __construct(string $serviceName, ?\Throwable $previous = null)
    {
        parent::__construct("Dịch vụ {$serviceName} tạm thời không khả dụng", 503, $previous);
    }
}
