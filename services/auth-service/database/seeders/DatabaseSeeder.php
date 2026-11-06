<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = Role::updateOrCreate(['code' => Role::ADMIN], ['name' => 'Quản trị viên']);
        $doctor = Role::updateOrCreate(['code' => Role::DOCTOR], ['name' => 'Bác sĩ']);
        $pharmacist = Role::updateOrCreate(['code' => Role::PHARMACIST], ['name' => 'Dược sĩ / Nhân viên kho']);

        User::firstOrCreate(
            ['email' => env('PIMS_ADMIN_EMAIL', 'admin@pims.local')],
            ['role_id' => $admin->id, 'name' => 'Quản trị viên', 'password' => Hash::make(env('PIMS_ADMIN_PASSWORD', 'Admin@123')), 'is_active' => true]
        );
        User::firstOrCreate(
            ['email' => 'doctor@pims.local'],
            ['role_id' => $doctor->id, 'name' => 'Bác sĩ Nguyễn An', 'password' => Hash::make('Doctor@123'), 'is_active' => true]
        );
        User::firstOrCreate(
            ['email' => 'pharmacist@pims.local'],
            ['role_id' => $pharmacist->id, 'name' => 'Dược sĩ Trần Minh', 'password' => Hash::make('Pharmacist@123'), 'is_active' => true]
        );
    }
}
