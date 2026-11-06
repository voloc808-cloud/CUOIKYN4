<?php

namespace Database\Seeders;

use App\Models\Prescription;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $samples = [
            ['code' => 'DT-SEED-001', 'patient' => 'Nguyễn Văn An', 'status' => Prescription::NEW],
            ['code' => 'DT-SEED-002', 'patient' => 'Trần Thị Bình', 'status' => Prescription::DISPENSED],
            ['code' => 'DT-SEED-003', 'patient' => 'Lê Văn Cường', 'status' => Prescription::REJECTED],
        ];
        foreach ($samples as $index => $sample) {
            $prescription = Prescription::updateOrCreate(['prescription_code' => $sample['code']], [
                'doctor_id' => 1, 'patient_name' => $sample['patient'], 'patient_code' => 'BN'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'diagnosis' => 'Dữ liệu đơn thuốc mẫu', 'status' => $sample['status'],
                'rejection_reason' => $sample['status'] === Prescription::REJECTED ? 'Đơn thuốc cần điều chỉnh' : null,
                'approved_by' => $sample['status'] !== Prescription::NEW ? 2 : null,
                'approved_at' => $sample['status'] === Prescription::DISPENSED ? Carbon::now()->subDay() : null,
                'dispensed_at' => $sample['status'] === Prescription::DISPENSED ? Carbon::now()->subDay() : null,
            ]);
            $prescription->details()->updateOrCreate(['medicine_id' => $index + 1], ['quantity' => 10 + $index * 5, 'instructions' => 'Uống sau ăn, ngày 2 lần']);
        }
    }
}
