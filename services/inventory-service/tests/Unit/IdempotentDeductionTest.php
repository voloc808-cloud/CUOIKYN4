<?php
namespace Tests\Unit;
use App\Support\IdempotentDeduction;
use PHPUnit\Framework\TestCase;
final class IdempotentDeductionTest extends TestCase
{
    public function test_repeated_prescription_returns_old_result_without_deducting_again(): void
    {
        $calls = 0; $old = ['prescription_id'=>10,'allocations'=>[['batch_id'=>1,'quantity'=>2]]];
        $result = (new IdempotentDeduction())->execute(['status'=>'DEDUCTED','result'=>$old], function () use (&$calls) { $calls++; return []; });
        self::assertSame($old, $result); self::assertSame(0, $calls);
    }
}
