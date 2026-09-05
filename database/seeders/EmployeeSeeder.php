<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Bank;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bankIds = Bank::limit(3)->pluck('id')->toArray();
        if (empty($bankIds)) {
            $bankIds = [null, null, null];
        }

        Employee::updateOrCreate(
            ['code' => 'EMP-2024-001'],
            [
                'name' => 'Budi Santoso',
                'ktp_number' => '3275010101900001',
                'marital_status' => 'TK',
                'phone' => '081234567890',
                'personal_email' => 'budi.santoso@example.com',
                'ktp_address' => 'Jl. Kebon Jeruk No. 12',
                'ktp_province' => 'DKI Jakarta',
                'ktp_city' => 'Jakarta Barat',
                'ktp_district' => 'Kebon Jeruk',
                'ktp_village' => 'Sukabumi Utara',
                'current_address' => 'Jl. Kebon Jeruk No. 12',
                'current_province' => 'DKI Jakarta',
                'current_city' => 'Jakarta Barat',
                'current_district' => 'Kebon Jeruk',
                'current_village' => 'Sukabumi Utara',
                'join_date' => Carbon::parse('2024-01-15'),
                'position' => 'Senior Developer',
                'bank_id' => $bankIds[0] ?? null,
                'bank_account_number' => '1234567890',
                'bank_account_name' => 'Budi Santoso',
                'username' => 'budi.santoso',
                'role' => 'Manager'
            ]
        );

        Employee::updateOrCreate(
            ['code' => 'EMP-2024-002'],
            [
                'name' => 'Siti Aminah',
                'ktp_number' => '3275010101900002',
                'marital_status' => 'K1',
                'phone' => '081234567891',
                'personal_email' => 'siti.aminah@example.com',
                'ktp_address' => 'Jl. Melati No. 5',
                'ktp_province' => 'Jawa Barat',
                'ktp_city' => 'Bandung',
                'ktp_district' => 'Coblong',
                'ktp_village' => 'Dago',
                'current_address' => 'Jl. Melati No. 5',
                'current_province' => 'Jawa Barat',
                'current_city' => 'Bandung',
                'current_district' => 'Coblong',
                'current_village' => 'Dago',
                'join_date' => Carbon::parse('2024-02-01'),
                'position' => 'Finance staff',
                'bank_id' => $bankIds[1] ?? ($bankIds[0] ?? null),
                'bank_account_number' => '0987654321',
                'bank_account_name' => 'Siti Aminah',
                'username' => 'siti.aminah',
                'role' => 'User'
            ]
        );

        Employee::updateOrCreate(
            ['code' => 'EMP-2024-003'],
            [
                'name' => 'Agus Setiawan',
                'ktp_number' => '3275010101900003',
                'marital_status' => 'K2',
                'phone' => '081234567892',
                'personal_email' => 'agus.setiawan@example.com',
                'ktp_address' => 'Jl. Sudirman No. 100',
                'ktp_province' => 'Banten',
                'ktp_city' => 'Tangerang',
                'ktp_district' => 'Cipondoh',
                'ktp_village' => 'Poris Plawad',
                'current_address' => 'Jl. Sudirman No. 100',
                'current_province' => 'Banten',
                'current_city' => 'Tangerang',
                'current_district' => 'Cipondoh',
                'current_village' => 'Poris Plawad',
                'join_date' => Carbon::parse('2024-03-10'),
                'position' => 'HR Operations',
                'bank_id' => $bankIds[2] ?? ($bankIds[0] ?? null),
                'bank_account_number' => '1122334455',
                'bank_account_name' => 'Agus Setiawan',
                'username' => 'agus.setiawan',
                'role' => 'User'
            ]
        );
    }
}
