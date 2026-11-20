<?php
namespace App\Services;
use App\Clients\InternalClient;
final class ReportService
{
    public function __construct(private readonly InternalClient $client) {}
    public function aggregate(string $from, string $to, string $token): array
    {
        $dates = compact('from', 'to');
        $stock = $this->client->get('kho', (string) env('SERVICE_INVENTORY_URL'), '/internal/v1/reports/stock', $token, $dates);
        $prescriptions = $this->client->get('đơn thuốc', (string) env('SERVICE_PRESCRIPTION_URL'), '/internal/v1/reports/prescriptions', $token, $dates);
        $medicines = $this->client->get('danh mục', (string) env('SERVICE_CATALOG_URL'), '/internal/v1/medicines/stock-thresholds', $token);
        $map = collect($medicines)->keyBy('id');
        $stock['rows'] = collect($stock['rows'])->map(function (array $row) use ($map): array { $medicine = $map->get($row['medicine_id'], []); return $row + ['medicine_code' => $medicine['code'] ?? null, 'medicine_name' => $medicine['name'] ?? "Thuốc #{$row['medicine_id']}"]; })->all();
        return ['from' => $from, 'to' => $to, 'stock' => $stock, 'prescriptions' => $prescriptions];
    }
}
