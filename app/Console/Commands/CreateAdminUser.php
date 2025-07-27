<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create';
    protected $description = 'Create admin user with proper roles';

    public function handle()
    {
        // Create admin role if it doesn't exist
        Role::firstOrCreate(['name' => 'admin']);

        // Delete existing admin user if exists
        User::where('email', 'admin@kpu.go.id')->delete();

        // Create new admin user
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@kpu.go.id',
            'password' => Hash::make('72e82b77'),
            'role' => 'admin',
            'nip' => '', // NIP format: 18 digit angka (YYYYMMDDNNNNNNNNN) atau kosong jika tidak tersedia
            'jabatan' => 'Administrator',
            'is_active' => true,
            'pangkat' => 'Administrator',
            'golongan' => '-',
            'unit_kerja' => 'KPU Kabupaten Cirebon',
        ]);

        // Assign admin role
        $admin->assignRole('admin');

        $this->info('Admin user created successfully!');
        $this->info('Email: admin@kpu.go.id');
        $this->info('Password: 72e82b77');
    }
}
