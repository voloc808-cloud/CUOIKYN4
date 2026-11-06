<?php
namespace App\Support;
use App\Exceptions\BusinessException;
use Carbon\CarbonImmutable;
final class FefoAllocator
{
    public function allocate(array $batches, float $required, ?CarbonImmutable $today = null): array
    {
        $today ??= CarbonImmutable::today();
        $available = array_values(array_filter($batches, fn (array $batch) => CarbonImmutable::parse($batch['expires_at'])->startOfDay()->greaterThanOrEqualTo($today) && (float) $batch['quantity_remaining'] > 0));
        usort($available, fn (array $left, array $right) => [$left['expires_at'], $left['id']] <=> [$right['expires_at'], $right['id']]);
        if (array_sum(array_column($available, 'quantity_remaining')) < $required) throw new BusinessException('Không đủ tồn kho khả dụng', 'INSUFFICIENT_STOCK', 409);
        $remaining = $required; $allocations = [];
        foreach ($available as $batch) { if ($remaining <= 0) break; $quantity = min($remaining, (float) $batch['quantity_remaining']); $allocations[] = ['batch_id' => (int) $batch['id'], 'quantity' => $quantity]; $remaining -= $quantity; }
        return $allocations;
    }
}
