<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\UnitConversion;
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
        $units = collect([
            ['code' => 'BOX', 'name' => 'Hộp'],
            ['code' => 'BLISTER', 'name' => 'Vỉ'],
            ['code' => 'TABLET', 'name' => 'Viên'],
            ['code' => 'BOTTLE', 'name' => 'Chai'],
            ['code' => 'TUBE', 'name' => 'Tuýp'],
            ['code' => 'AMPOULE', 'name' => 'Ống'],
        ])->mapWithKeys(fn (array $unit) => [$unit['code'] => Unit::updateOrCreate(['code' => $unit['code']], $unit)]);

        $catalog = [
            ['MED001', 'Paracetamol 500mg', 'Paracetamol', '500 mg', 'Viên nén'],
            ['MED002', 'Amoxicillin 500mg', 'Amoxicillin', '500 mg', 'Viên nang'],
            ['MED003', 'Azithromycin 250mg', 'Azithromycin', '250 mg', 'Viên nén'],
            ['MED004', 'Cefixime 200mg', 'Cefixime', '200 mg', 'Viên nang'],
            ['MED005', 'Ibuprofen 400mg', 'Ibuprofen', '400 mg', 'Viên nén'],
            ['MED006', 'Loratadine 10mg', 'Loratadine', '10 mg', 'Viên nén'],
            ['MED007', 'Omeprazole 20mg', 'Omeprazole', '20 mg', 'Viên nang'],
            ['MED008', 'Metformin 500mg', 'Metformin', '500 mg', 'Viên nén'],
            ['MED009', 'Amlodipine 5mg', 'Amlodipine', '5 mg', 'Viên nén'],
            ['MED010', 'Losartan 50mg', 'Losartan', '50 mg', 'Viên nén'],
            ['MED011', 'Atorvastatin 20mg', 'Atorvastatin', '20 mg', 'Viên nén'],
            ['MED012', 'Salbutamol 2mg', 'Salbutamol', '2 mg', 'Viên nén'],
            ['MED013', 'Vitamin C 500mg', 'Ascorbic acid', '500 mg', 'Viên sủi'],
            ['MED014', 'ORS', 'Glucose và điện giải', 'Gói 27.9 g', 'Bột pha uống'],
            ['MED015', 'Povidone Iodine 10%', 'Povidone iodine', '10%', 'Dung dịch'],
            ['MED016', 'NaCl 0.9%', 'Sodium chloride', '500 ml', 'Dung dịch truyền'],
            ['MED017', 'Diclofenac gel', 'Diclofenac sodium', '1%', 'Gel bôi'],
            ['MED018', 'Insulin Regular', 'Human insulin', '100 IU/ml', 'Dung dịch tiêm'],
            ['MED019', 'Acetylcysteine 200mg', 'Acetylcysteine', '200 mg', 'Gói bột'],
            ['MED020', 'Domperidone 10mg', 'Domperidone', '10 mg', 'Viên nén'],
        ];

        foreach ($catalog as [$code, $name, $ingredient, $strength, $form]) {
            $medicine = Medicine::updateOrCreate(['code' => $code], [
                'base_unit_id' => $units['TABLET']->id,
                'name' => $name,
                'active_ingredient' => $ingredient,
                'strength' => $strength,
                'dosage_form' => $form,
                'minimum_stock' => 100,
                'is_active' => true,
            ]);
            UnitConversion::updateOrCreate(
                ['medicine_id' => $medicine->id, 'from_unit_id' => $units['BOX']->id, 'to_unit_id' => $units['TABLET']->id],
                ['factor' => 100]
            );
            UnitConversion::updateOrCreate(
                ['medicine_id' => $medicine->id, 'from_unit_id' => $units['BLISTER']->id, 'to_unit_id' => $units['TABLET']->id],
                ['factor' => 10]
            );
        }

        foreach ([
            ['SUP001', 'Công ty Dược Hậu Giang', '0901000001', 'contact@dhg.example'],
            ['SUP002', 'Công ty Dược phẩm Imexpharm', '0901000002', 'contact@imex.example'],
            ['SUP003', 'Công ty Traphaco', '0901000003', 'contact@traphaco.example'],
            ['SUP004', 'Công ty Pymepharco', '0901000004', 'contact@pyme.example'],
        ] as [$code, $name, $phone, $email]) {
            Supplier::updateOrCreate(['code' => $code], compact('name', 'phone', 'email') + ['is_active' => true]);
        }
    }
}
