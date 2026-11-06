<?php
namespace App\Support;
use App\Exceptions\BusinessException;
use App\Models\Prescription;
final class PrescriptionTransition
{
    private const ALLOWED = [Prescription::NEW => [Prescription::APPROVED, Prescription::REJECTED], Prescription::APPROVED => [Prescription::DISPENSED, Prescription::NEW]];
    public function assert(string $from, string $to): void { if (!in_array($to, self::ALLOWED[$from] ?? [], true)) throw new BusinessException("Không thể chuyển trạng thái từ {$from} sang {$to}", 'INVALID_STATUS_TRANSITION', 409); }
}
