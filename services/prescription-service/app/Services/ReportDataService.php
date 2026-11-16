<?php
namespace App\Services;
use App\Models\Prescription;
final class ReportDataService
{
    public function prescriptions(string $from, string $to): array
    {
        $items = Prescription::with('details')->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])->latest()->get();
        return ['from' => $from, 'to' => $to, 'summary' => ['total' => $items->count(), 'new' => $items->where('status', Prescription::NEW)->count(), 'dispensed' => $items->where('status', Prescription::DISPENSED)->count(), 'rejected' => $items->where('status', Prescription::REJECTED)->count()], 'items' => $items];
    }
}
