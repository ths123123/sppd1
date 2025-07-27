<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = '72e82b77';
        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $nip = '1970' . str_pad($i, 14, rand(1,9), STR_PAD_RIGHT); // NIP unik dan panjang
            $email = "staf$i@kpu.go.id";
            // Hapus user dengan NIP/email ini jika sudah ada
            User::where('nip', $nip)->orWhere('email', $email)->delete();
            $users[] = [
                'name' => "Staff $i KPU Cirebon",
                'email' => $email,
                'nip' => $nip,
                'jabatan' => 'Staff',
                'role' => 'staff',
                'phone' => '0812'.str_pad($i, 8, '12345678', STR_PAD_RIGHT),
                'address' => "Jl. Mawar No.$i, Cirebon",
                'pangkat' => 'Penata Muda',
                'golongan' => 'III/a',
                'unit_kerja' => 'Sekretariat KPU Cirebon',
                'is_active' => true,
                'email_verified_at' => now(),
                'avatar' => null,
                'bio' => 'Staff KPU Cirebon',
                'department' => 'Umum',
                'employee_id' => 'EMP'.str_pad($i,3,'0',STR_PAD_LEFT),
                'birth_date' => '1990-01-'.str_pad((($i%28)+1),2,'0',STR_PAD_LEFT),
                'gender' => ($i%2==0 ? 'male' : 'female'),
                'password' => $password,
                'remember_token' => Str::random(10),
            ];
        }
        User::insert($users);
    }
} 