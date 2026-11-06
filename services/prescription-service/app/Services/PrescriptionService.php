<?php
namespace App\Services;
use App\Clients\CatalogClient;
use App\Clients\InventoryClient;
use App\Exceptions\BusinessException;
use App\Models\Prescription;
use App\Repositories\PrescriptionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Support\ApprovalSaga;
use App\Support\PrescriptionTransition;
use Pims\Common\Exceptions\ServiceUnavailableException;
final class PrescriptionService
{
    public function __construct(private readonly PrescriptionRepository $repository, private readonly CatalogClient $catalog, private readonly InventoryClient $inventory, private readonly PrescriptionTransition $transition, private readonly ApprovalSaga $saga) {}
    public function list(string $role, int $userId, string $status = '') { return $this->repository->paginate($role, $userId, $status); }
    public function show(int $id, string $role, int $userId, string $token): Prescription
    {
        $prescription = $this->repository->find($id);
        if ($role === 'DOCTOR' && (int) $prescription->doctor_id !== $userId) throw new BusinessException('Bạn không được xem đơn thuốc này', 'FORBIDDEN', 403);
        $this->enrich($prescription, $token); return $prescription;
    }
    public function create(array $data, int $doctorId, string $token): Prescription
    {
        foreach ($data['items'] as $item) $this->catalog->medicine((int) $item['medicine_id'], $token);
        return DB::transaction(function () use ($data, $doctorId): Prescription {
            $prescription = Prescription::create(['prescription_code' => 'DT-'.\Illuminate\Support\Carbon::now()->format('YmdHis').'-'.random_int(100, 999), 'doctor_id' => $doctorId, 'patient_name' => $data['patient_name'], 'patient_code' => $data['patient_code'] ?? null, 'diagnosis' => $data['diagnosis'] ?? null, 'status' => Prescription::NEW]);
            foreach ($data['items'] as $item) $prescription->details()->create($item);
            return $prescription->load('details');
        });
    }
    public function approve(int $id, int $pharmacistId, string $token, string $correlationId): Prescription
    {
        $prescription = $this->repository->find($id);
        if ($prescription->status === Prescription::APPROVED) {
            $status = $this->inventory->deductionStatus($id, $token);
            if ($status === 'DEDUCTED') {
                $prescription->update(['status' => Prescription::DISPENSED, 'dispensed_at' => \Illuminate\Support\Carbon::now()]);
                return $prescription->fresh('details');
            }
            if ($status === 'RESTORED') {
                $prescription->update(['status' => Prescription::NEW, 'approved_by' => null, 'approved_at' => null]);
                throw new BusinessException('Kho đã hoàn giao dịch; vui lòng duyệt lại đơn', 'INVENTORY_RESTORED', 409);
            }
            if ($status !== 'NOT_FOUND') {
                throw new BusinessException('Đơn thuốc cần đối soát kho trước khi duyệt lại', 'INVENTORY_RECONCILIATION_REQUIRED', 503);
            }
            $items = $prescription->details->map(fn ($item) => ['medicine_id' => (int) $item->medicine_id, 'quantity' => (float) $item->quantity])->all();
            $check = $this->inventory->check($items, $token);
            if (!($check['available'] ?? false)) {
                $prescription->update(['status' => Prescription::NEW, 'approved_by' => null, 'approved_at' => null]);
                throw new BusinessException('Không đủ tồn kho khả dụng', 'INSUFFICIENT_STOCK', 409);
            }
            try {
                $this->inventory->deduct($id, $items, $token);
            } catch (ServiceUnavailableException $exception) {
                throw $exception;
            } catch (Throwable $exception) {
                $prescription->update(['status' => Prescription::NEW, 'approved_by' => null, 'approved_at' => null]);
                throw $exception;
            }
            try {
                $prescription->update(['status' => Prescription::DISPENSED, 'dispensed_at' => \Illuminate\Support\Carbon::now()]);
            } catch (Throwable $exception) {
                $this->inventory->restore($id, $token);
                $prescription->update(['status' => Prescription::NEW, 'approved_by' => null, 'approved_at' => null]);
                throw $exception;
            }
            return $prescription->fresh('details');
        }
        $this->assertNew($prescription);
        $items = $prescription->details->map(fn ($item) => ['medicine_id' => (int) $item->medicine_id, 'quantity' => (float) $item->quantity])->all();
        $this->saga->run(
            function () use ($items, $token, $correlationId, $id): void { $this->log($correlationId, $id, 'checking_stock'); $check = $this->inventory->check($items, $token); if (!($check['available'] ?? false)) throw new BusinessException('Không đủ tồn kho khả dụng', 'INSUFFICIENT_STOCK', 409); },
            function () use ($prescription, $pharmacistId): void { $this->transition->assert($prescription->status, Prescription::APPROVED); $prescription->update(['status' => Prescription::APPROVED, 'approved_by' => $pharmacistId, 'approved_at' => \Illuminate\Support\Carbon::now()]); },
            function () use ($id, $items, $token, $correlationId): void { $this->log($correlationId, $id, 'deducting_fefo'); $this->inventory->deduct($id, $items, $token); },
            function () use ($prescription): void { $this->transition->assert($prescription->status, Prescription::DISPENSED); $prescription->update(['status' => Prescription::DISPENSED, 'dispensed_at' => \Illuminate\Support\Carbon::now()]); },
            function () use ($prescription, $correlationId, $id): void { $prescription->update(['status' => Prescription::NEW, 'approved_by' => null, 'approved_at' => null]); $this->log($correlationId, $id, 'rolled_back'); },
            function () use ($id, $token, $correlationId): void { $this->log($correlationId, $id, 'restoring_stock'); $this->inventory->restore($id, $token); },
            function () use ($id, $token, $correlationId): bool {
                $this->log($correlationId, $id, 'reconciling_deduction');
                if ($this->inventory->deductionStatus($id, $token) !== 'DEDUCTED') return false;
                $this->inventory->restore($id, $token);
                return true;
            }
        );
        $this->log($correlationId, $id, 'dispensed'); return $prescription->fresh('details');
    }
    public function reject(int $id, string $reason, int $pharmacistId): Prescription
    {
        $prescription = $this->repository->find($id); $this->assertNew($prescription); $this->transition->assert($prescription->status, Prescription::REJECTED);
        $prescription->update(['status' => Prescription::REJECTED, 'rejection_reason' => $reason, 'approved_by' => $pharmacistId]); return $prescription;
    }
    private function assertNew(Prescription $prescription): void { if ($prescription->status !== Prescription::NEW) throw new BusinessException('Trạng thái đơn thuốc không hợp lệ', 'INVALID_STATE_TRANSITION', 409); }
    private function enrich(Prescription $prescription, string $token): void { foreach ($prescription->details as $detail) $detail->setAttribute('medicine', $this->catalog->medicine((int) $detail->medicine_id, $token)); }
    private function log(string $correlationId, int $id, string $step): void { Log::info('prescription_saga', ['correlation_id' => $correlationId, 'prescription_id' => $id, 'step' => $step]); }
}
