<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing users with role 'pegawai' to 'staff'
        DB::table('users')
            ->where('role', 'pegawai')
            ->update(['role' => 'staff']);

        // Update role constraint to include 'staff' instead of 'pegawai'
        // First, drop the existing constraint
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        
        // Add new constraint with 'staff', 'kasubbag', 'sekretaris', 'ppk', 'admin' only
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('staff', 'kasubbag', 'sekretaris', 'ppk', 'admin'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu revert ke pegawai lagi, biarkan tetap staff
        // DB::table('users')
        //     ->where('role', 'staff')
        //     ->update(['role' => 'pegawai']);

        // Revert constraint back to original
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('staff', 'kasubbag', 'sekretaris', 'ppk', 'admin'))");
    }
};
