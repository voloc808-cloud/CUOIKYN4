<?php
namespace App\Services;
use App\Clients\CatalogClient;
use App\Models\AlertSetting;
use App\Models\Batch;
final class AlertService
{
    public function __construct(private readonly CatalogClient $catalog) {}
    public function alerts(string $token): array
    {
        $setting = AlertSetting::firstOrCreate([], ['expiry_warning_days' => 30]); $today = \Illuminate\Support\Carbon::now()->startOfDay(); $limit = $today->copy()->addDays($setting->expiry_warning_days);
        $batches = Batch::where('quantity_remaining', '>', 0)->whereDate('expires_at', '<=', $limit->toDateString())->orderBy('expires_at')->get();
        $thresholds = collect($this->catalog->stockThresholds($token)); $medicineMap = $thresholds->keyBy('id');
        $expiry = $batches->map(function ($batch) use ($today, $medicineMap) {
            $medicine = $medicineMap->get((int) $batch->medicine_id, []); $expired = $batch->expires_at->startOfDay()->lt($today);
            return ['level' => $expired ? 'CRITICAL' : 'WARNING', 'medicine_id' => $batch->medicine_id, 'medicine_code' => $medicine['code'] ?? null, 'medicine_name' => $medicine['name'] ?? null, 'batch_id' => $batch->id, 'batch_code' => $batch->batch_code, 'expires_at' => $batch->expires_at->toDateString(), 'quantity_remaining' => $batch->quantity_remaining];
        })->values();
        $available = Batch::whereDate('expires_at', '>=', $today->toDateString())->where('quantity_remaining', '>', 0)->selectRaw('medicine_id, SUM(quantity_remaining) as available')->groupBy('medicine_id')->pluck('available', 'medicine_id');
        $lowStock = $thresholds->filter(fn ($medicine) => (float) ($available[(int) $medicine['id']] ?? 0) < (float) $medicine['minimum_stock'])->map(fn ($medicine) => ['level' => 'WARNING', 'medicine_id' => $medicine['id'], 'medicine_code' => $medicine['code'], 'medicine_name' => $medicine['name'], 'available' => (float) ($available[(int) $medicine['id']] ?? 0), 'minimum_stock' => (float) $medicine['minimum_stock']])->values();
        return ['settings' => $setting, 'expiry' => $expiry, 'low_stock' => $lowStock, 'summary' => ['critical' => $expiry->where('level', 'CRITICAL')->count(), 'expiring' => $expiry->where('level', 'WARNING')->count(), 'low_stock' => $lowStock->count()]];
    }
    public function update(array $data): AlertSetting { $setting = AlertSetting::firstOrCreate([], ['expiry_warning_days' => 30]); $setting->update($data); return $setting; }
}
