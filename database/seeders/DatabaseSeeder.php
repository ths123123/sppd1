<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Setting;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
                    UserRoleSeeder::class,
            // DummyTravelRequestSeeder::class, // Seeder untuk dummy SPPD kasubbag1 (hapus, akan dipanggil dari UserRoleSeeder)
            NotificationSeeder::class, // Seeder untuk notifikasi dummy
            // Tambahkan seeder lain yang esensial di sini jika ada
        ]);
    }
}
