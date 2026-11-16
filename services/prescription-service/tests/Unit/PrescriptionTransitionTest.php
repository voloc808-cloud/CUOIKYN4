<?php
namespace Tests\Unit;
use App\Exceptions\BusinessException;
use App\Models\Prescription;
use App\Support\PrescriptionTransition;
use PHPUnit\Framework\TestCase;
final class PrescriptionTransitionTest extends TestCase
{
    public function test_accepts_valid_transitions(): void { $guard = new PrescriptionTransition(); $guard->assert(Prescription::NEW, Prescription::APPROVED); $guard->assert(Prescription::APPROVED, Prescription::DISPENSED); $guard->assert(Prescription::NEW, Prescription::REJECTED); self::assertTrue(true); }
    public function test_rejects_skipping_directly_to_dispensed(): void { $this->expectException(BusinessException::class); (new PrescriptionTransition())->assert(Prescription::NEW, Prescription::DISPENSED); }
}
