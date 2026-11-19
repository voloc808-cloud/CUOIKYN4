<?php
namespace Tests\Unit;
use App\Exceptions\BusinessException;
use App\Support\FefoAllocator;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;
final class FefoAllocatorTest extends TestCase
{
    private FefoAllocator $allocator;
    protected function setUp(): void { $this->allocator = new FefoAllocator(); }
    public function test_allocates_from_earliest_expiry_across_multiple_batches(): void
    {
        $result = $this->allocator->allocate([['id'=>2,'expires_at'=>'2027-06-01','quantity_remaining'=>10],['id'=>1,'expires_at'=>'2027-01-01','quantity_remaining'=>5]], 8, CarbonImmutable::parse('2026-01-01'));
        self::assertSame([['batch_id'=>1,'quantity'=>5.0],['batch_id'=>2,'quantity'=>3.0]], $result);
    }
    public function test_ignores_expired_batches(): void
    {
        $result = $this->allocator->allocate([['id'=>1,'expires_at'=>'2025-12-31','quantity_remaining'=>100],['id'=>2,'expires_at'=>'2026-02-01','quantity_remaining'=>7]], 7, CarbonImmutable::parse('2026-01-01'));
        self::assertSame([['batch_id'=>2,'quantity'=>7.0]], $result);
    }
    public function test_throws_before_allocating_when_total_stock_is_insufficient(): void
    {
        $this->expectException(BusinessException::class); $this->expectExceptionMessage('Không đủ tồn kho');
        $this->allocator->allocate([['id'=>1,'expires_at'=>'2027-01-01','quantity_remaining'=>2]], 3, CarbonImmutable::parse('2026-01-01'));
    }
}
