<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Non-aktifkan observer sementara untuk mencegah aksi yang tidak diinginkan saat seeding
        User::withoutEvents(function () {
            // Hapus semua user yang ada untuk memastikan kebersihan data
            User::truncate();

            $password = Hash::make('password123'); // Password default yang aman

            // === PEMBUATAN PENGGUNA DENGAN PERAN SPESIFIK ===

            // 1. Sekretaris
            User::create([
                'name' => 'Drs. Ahmad Supriyadi, M.Si',
                'email' => 'sekretaris@kpu.go.id',
                'nip' => '196505151990031001',
                'password' => $password,
                'jabatan' => 'Sekretaris',
                'role' => 'sekretaris',
                'phone' => '081234567890',
                'address' => 'Jl. Siliwangi No. 15, Cirebon',
                'pangkat' => 'Pembina Tk.I',
                'golongan' => 'IV/b',
                'unit_kerja' => 'Sekretariat KPU Kabupaten Cirebon',
                'is_active' => true,
                'gender' => 'male',
                'department' => 'Sekretariat',
            ]);

            // 2. Pejabat Pembuat Komitmen (PPK)
            User::create([
                'name' => 'Ir. Siti Nurhaliza, M.M',
                'email' => 'ppk@kpu.go.id',
                'nip' => '197203201995032001',
                'password' => $password,
                'jabatan' => 'Pejabat Pembuat Komitmen',
                'role' => 'ppk',
                'phone' => '081234567891',
                'address' => 'Jl. Veteran No. 8, Cirebon',
                'pangkat' => 'Pembina',
                'golongan' => 'IV/a',
                'unit_kerja' => 'Sekretariat KPU Kabupaten Cirebon',
                'is_active' => true,
                'gender' => 'female',
                'department' => 'Keuangan',
            ]);

            // 3. Kepala Sub Bagian (Kasubbag) - 4 Akun
            $kasubbagData = [
                [
                    'name' => 'Drs. Bambang Sutrisno',
                    'email' => 'kasubbag.umum@kpu.go.id',
                    'nip' => '198001151998031001',
                    'jabatan' => 'Kepala Sub Bagian Umum',
                    'unit_kerja' => 'Sub Bagian Umum',
                    'department' => 'Umum',
                    'pangkat' => 'Penata Tk.I',
                    'golongan' => 'III/d',
                ],
                [
                    'name' => 'Siti Aminah, S.Sos',
                    'email' => 'kasubbag.keuangan@kpu.go.id',
                    'nip' => '198504201998032001',
                    'jabatan' => 'Kepala Sub Bagian Keuangan',
                    'unit_kerja' => 'Sub Bagian Keuangan',
                    'department' => 'Keuangan',
                    'pangkat' => 'Penata Tk.I',
                    'golongan' => 'III/d',
                ],
                [
                    'name' => 'Ir. Rudi Hartono',
                    'email' => 'kasubbag.teknis@kpu.go.id',
                    'nip' => '198208101999031001',
                    'jabatan' => 'Kepala Sub Bagian Teknis',
                    'unit_kerja' => 'Sub Bagian Teknis',
                    'department' => 'Teknis',
                    'pangkat' => 'Penata Tk.I',
                    'golongan' => 'III/d',
                ],
                [
                    'name' => 'Dra. Endang Sulistyowati',
                    'email' => 'kasubbag.humas@kpu.go.id',
                    'nip' => '198612251999032001',
                    'jabatan' => 'Kepala Sub Bagian Humas',
                    'unit_kerja' => 'Sub Bagian Humas',
                    'department' => 'Humas',
                    'pangkat' => 'Penata Tk.I',
                    'golongan' => 'III/d',
                ],
            ];

            foreach ($kasubbagData as $index => $data) {
                User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'nip' => $data['nip'],
                    'password' => $password,
                    'jabatan' => $data['jabatan'],
                    'role' => 'kasubbag',
                    'phone' => '08123456789' . ($index + 2),
                    'address' => 'Jl. Siliwangi No. 15, Cirebon',
                    'pangkat' => $data['pangkat'],
                    'golongan' => $data['golongan'],
                    'unit_kerja' => $data['unit_kerja'],
                    'is_active' => true,
                    'gender' => $index % 2 == 0 ? 'male' : 'female',
                    'department' => $data['department'],
                ]);
            }

            // 4. Staff - 10 Akun
            $staffData = [
                ['name' => 'Ahmad Fauzi, S.Kom', 'department' => 'Teknis', 'unit_kerja' => 'Sub Bagian Teknis'],
                ['name' => 'Nurul Hidayah, S.E', 'department' => 'Keuangan', 'unit_kerja' => 'Sub Bagian Keuangan'],
                ['name' => 'Dedi Kurniawan, S.Sos', 'department' => 'Umum', 'unit_kerja' => 'Sub Bagian Umum'],
                ['name' => 'Rina Marlina, S.Pd', 'department' => 'Humas', 'unit_kerja' => 'Sub Bagian Humas'],
                ['name' => 'Eko Prasetyo, S.T', 'department' => 'Teknis', 'unit_kerja' => 'Sub Bagian Teknis'],
                ['name' => 'Yuni Safitri, S.E', 'department' => 'Keuangan', 'unit_kerja' => 'Sub Bagian Keuangan'],
                ['name' => 'Budi Santoso, S.Sos', 'department' => 'Umum', 'unit_kerja' => 'Sub Bagian Umum'],
                ['name' => 'Dewi Sartika, S.Pd', 'department' => 'Humas', 'unit_kerja' => 'Sub Bagian Humas'],
                ['name' => 'Agus Setiawan, S.Kom', 'department' => 'Teknis', 'unit_kerja' => 'Sub Bagian Teknis'],
                ['name' => 'Sari Indah, S.E', 'department' => 'Keuangan', 'unit_kerja' => 'Sub Bagian Keuangan'],
            ];

            foreach ($staffData as $index => $data) {
                $nip = '2000' . str_pad(($index + 1), 2, '0', STR_PAD_LEFT) . '15' . str_pad(($index + 1), 6, '0', STR_PAD_LEFT);
                User::create([
                    'name' => $data['name'],
                    'email' => 'staff' . ($index + 1) . '@kpu.go.id',
                    'nip' => $nip,
                    'password' => $password,
                    'jabatan' => 'Staff Pelaksana',
                    'role' => 'staff',
                    'phone' => '0812345678' . str_pad(($index + 10), 2, '0', STR_PAD_LEFT),
                    'address' => 'Jl. Siliwangi No. 15, Cirebon',
                    'pangkat' => 'Pengatur',
                    'golongan' => 'II/c',
                    'unit_kerja' => $data['unit_kerja'],
                    'is_active' => true,
                    'gender' => $index % 2 == 0 ? 'male' : 'female',
                    'department' => $data['department'],
                ]);
            }

            // 5. Admin (Opsional, tapi sangat direkomendasikan)
            User::create([
                'name' => 'Admin Sistem SPPD',
                'email' => 'admin@kpu.go.id',
                'nip' => '199001011990010001',
                'password' => $password,
                'jabatan' => 'Administrator Sistem',
                'role' => 'admin',
                'phone' => '081234567800',
                'address' => 'Jl. Siliwangi No. 15, Cirebon',
                'pangkat' => 'Penata Tk.I',
                'golongan' => 'III/d',
                'unit_kerja' => 'Sekretariat KPU Kabupaten Cirebon',
                'is_active' => true,
                'gender' => 'male',
                'department' => 'IT',
            ]);
        });

        $this->command->info('✅ Seeding pengguna berhasil!');
        $this->command->info('📊 Total user yang dibuat:');
        $this->command->info('   • 1 Sekretaris');
        $this->command->info('   • 1 PPK');
        $this->command->info('   • 4 Kasubbag');
        $this->command->info('   • 10 Staff');
        $this->command->info('   • 1 Admin');
        $this->command->info('');
        $this->command->info('🔑 Password default untuk semua akun: password123');
        $this->command->info('📧 Email format: role@kpu.go.id atau staff1@kpu.go.id - staff10@kpu.go.id');
    }
}
