<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['admin', 'staff', 'kasubbag', 'sekretaris', 'ppk'];

        foreach ($roles as $roleName) {
            Role::findOrCreate($roleName);
        }

        $this->command->info('✅ Roles seeded successfully!');
        $this->command->info('Roles created: ' . implode(', ', $roles));
    }
}
