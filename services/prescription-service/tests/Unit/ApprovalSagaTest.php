<?php
namespace Tests\Unit;
use App\Support\ApprovalSaga;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Pims\Common\Exceptions\ServiceUnavailableException;
final class ApprovalSagaTest extends TestCase
{
    public function test_inventory_failure_rolls_prescription_back_to_new(): void
    {
        $status = 'NEW'; $saga = new ApprovalSaga();
        try { $saga->run(fn()=>true, function()use(&$status){$status='APPROVED';}, fn()=>throw new RuntimeException('inventory down'), function()use(&$status){$status='DISPENSED';}, function()use(&$status){$status='NEW';}, fn()=>true); self::fail('Expected exception'); } catch (RuntimeException) { self::assertSame('NEW', $status); }
    }
    public function test_final_update_failure_restores_stock_and_rolls_back(): void
    {
        $status = 'NEW'; $restored = false; $saga = new ApprovalSaga();
        try { $saga->run(fn()=>true, function()use(&$status){$status='APPROVED';}, fn()=>true, fn()=>throw new RuntimeException('db error'), function()use(&$status){$status='NEW';}, function()use(&$restored){$restored=true;}); self::fail('Expected exception'); } catch (RuntimeException) { self::assertTrue($restored); self::assertSame('NEW', $status); }
    }
    public function test_ambiguous_timeout_keeps_approved_until_stock_is_reconciled(): void
    {
        $status = 'NEW';
        try {
            (new ApprovalSaga())->run(
                fn () => true,
                function () use (&$status) { $status = 'APPROVED'; },
                fn () => throw new ServiceUnavailableException('kho'),
                fn () => true,
                function () use (&$status) { $status = 'NEW'; },
                fn () => true,
                fn () => false
            );
            self::fail('Expected timeout');
        } catch (ServiceUnavailableException) {
            self::assertSame('APPROVED', $status);
        }
    }

    public function test_confirmed_deduction_is_restored_before_rollback(): void
    {
        $status = 'NEW'; $restored = false;
        try {
            (new ApprovalSaga())->run(
                fn () => true,
                function () use (&$status) { $status = 'APPROVED'; },
                fn () => throw new ServiceUnavailableException('kho'),
                fn () => true,
                function () use (&$status) { $status = 'NEW'; },
                function () use (&$restored) { $restored = true; },
                function () use (&$restored) { $restored = true; return true; }
            );
            self::fail('Expected timeout');
        } catch (ServiceUnavailableException) {
            self::assertTrue($restored);
            self::assertSame('NEW', $status);
        }
    }
}
