<?php
namespace App\Services;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
final class ReportDataService
{
    public function stock(string $from, string $to): array
    {
        $fromDate = $from.' 00:00:00'; $toDate = $to.' 23:59:59';
        $medicineIds = StockMovement::query()->distinct()->pluck('medicine_id'); $rows = [];
        foreach ($medicineIds as $medicineId) {
            $base = StockMovement::where('medicine_id', $medicineId);
            $openingMovements = (clone $base)->where('created_at', '<', $fromDate)->get();
            $period = (clone $base)->whereBetween('created_at', [$fromDate, $toDate])->get();
            $opening = $openingMovements->sum(fn ($item) => $this->signed($item->movement_type, (float) $item->quantity));
            $imports = $period->where('movement_type', StockMovement::IMPORT)->sum('quantity');
            $restores = $period->where('movement_type', StockMovement::RESTORE_PRESCRIPTION)->sum('quantity');
            $prescriptionExports = $period->where('movement_type', StockMovement::EXPORT_PRESCRIPTION)->sum('quantity');
            $disposalExports = $period->where('movement_type', StockMovement::EXPORT_DISPOSAL)->sum('quantity');
            $rows[] = ['medicine_id' => (int) $medicineId, 'opening' => (float) $opening, 'imports' => (float) $imports, 'restores' => (float) $restores, 'prescription_exports' => (float) $prescriptionExports, 'disposal_exports' => (float) $disposalExports, 'closing' => (float) ($opening + $imports + $restores - $prescriptionExports - $disposalExports)];
        }
        return ['from' => $from, 'to' => $to, 'rows' => $rows, 'totals' => ['imports' => array_sum(array_column($rows, 'imports')), 'prescription_exports' => array_sum(array_column($rows, 'prescription_exports')), 'disposal_exports' => array_sum(array_column($rows, 'disposal_exports')), 'closing' => array_sum(array_column($rows, 'closing'))]];
    }
    private function signed(string $type, float $quantity): float { return in_array($type, [StockMovement::IMPORT, StockMovement::RESTORE_PRESCRIPTION], true) ? $quantity : -$quantity; }
}
