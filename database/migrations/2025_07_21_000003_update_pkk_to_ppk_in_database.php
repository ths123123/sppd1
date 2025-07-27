<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update role 'pkk' menjadi 'ppk' di tabel users
        DB::table('users')->where('role', 'pkk')->update(['role' => 'ppk']);
        // Update role 'pkk' menjadi 'ppk' di tabel approvals (jika ada)
        if (Schema::hasTable('approvals')) {
            DB::table('approvals')->where('role', 'pkk')->update(['role' => 'ppk']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan role 'ppk' menjadi 'pkk' jika rollback
        DB::table('users')->where('role', 'ppk')->update(['role' => 'pkk']);
        if (Schema::hasTable('approvals')) {
            DB::table('approvals')->where('role', 'ppk')->update(['role' => 'pkk']);
        }
    }
}; 