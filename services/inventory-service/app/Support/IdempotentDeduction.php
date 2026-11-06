<?php
namespace App\Support;
use App\Exceptions\BusinessException;
final class IdempotentDeduction
{
    public function execute(?array $operation, callable $deduct): array
    {
        if (($operation['status'] ?? null) === 'DEDUCTED') return $operation['result'];
        if (($operation['status'] ?? null) === 'RESTORED') throw new BusinessException('Đơn thuốc đã được hoàn kho', 'PRESCRIPTION_ALREADY_RESTORED');
        return $deduct();
    }
}
