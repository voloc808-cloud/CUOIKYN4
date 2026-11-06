<?php
namespace App\Support;
use App\Exceptions\BusinessException;
use Carbon\CarbonImmutable;
final class BatchRules
{
    public function validate(array $item, callable $duplicate): void
    {
        if ((float) $item['quantity'] <= 0) throw new BusinessException('Số lượng nhập phải lớn hơn 0', 'INVALID_BATCH_QUANTITY');
        if (CarbonImmutable::parse($item['expires_at'])->lessThanOrEqualTo(CarbonImmutable::parse($item['manufactured_at']))) throw new BusinessException('Hạn sử dụng phải sau ngày sản xuất', 'INVALID_EXPIRY_DATE');
        if ($duplicate((int) $item['medicine_id'], (string) $item['batch_code'])) throw new BusinessException('Mã lô đã tồn tại trong cùng thuốc', 'BATCH_CODE_DUPLICATED');
    }
}
