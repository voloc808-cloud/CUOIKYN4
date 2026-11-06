<?php
namespace Tests\Unit;
use App\Exceptions\BusinessException;
use App\Support\BatchRules;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
final class BatchRulesTest extends TestCase
{
    public static function invalidBatches(): array { return [[['medicine_id'=>1,'batch_code'=>'A','manufactured_at'=>'2026-02-01','expires_at'=>'2026-01-01','quantity'=>1],'INVALID_EXPIRY_DATE'],[['medicine_id'=>1,'batch_code'=>'A','manufactured_at'=>'2026-01-01','expires_at'=>'2027-01-01','quantity'=>0],'INVALID_BATCH_QUANTITY']]; }
    #[DataProvider('invalidBatches')]
    public function test_rejects_invalid_batch(array $item, string $code): void { try { (new BatchRules())->validate($item, fn () => false); self::fail('Expected exception'); } catch (BusinessException $exception) { self::assertSame($code, $exception->errorCode); } }
    public function test_rejects_duplicate_code_for_same_medicine(): void { try { (new BatchRules())->validate(['medicine_id'=>1,'batch_code'=>'A','manufactured_at'=>'2026-01-01','expires_at'=>'2027-01-01','quantity'=>1], fn () => true); self::fail('Expected exception'); } catch (BusinessException $exception) { self::assertSame('BATCH_CODE_DUPLICATED', $exception->errorCode); } }
}
