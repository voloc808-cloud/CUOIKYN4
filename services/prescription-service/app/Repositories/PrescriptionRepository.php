<?php
namespace App\Repositories;
use App\Models\Prescription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
final class PrescriptionRepository
{
    public function paginate(string $role, int $userId, string $status = ''): LengthAwarePaginator
    {
        return Prescription::with('details')->when($role === 'DOCTOR', fn ($q) => $q->where('doctor_id', $userId))
            ->when($status !== '', fn ($q) => $q->where('status', $status))->latest()->paginate(15);
    }
    public function find(int $id): Prescription { return Prescription::with('details')->findOrFail($id); }
}
