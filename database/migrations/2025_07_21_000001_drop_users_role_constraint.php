<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop constraint enum role pada tabel users
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
    }

    public function down(): void
    {
        // Tidak perlu add constraint di down, biar migration berikutnya yang handle
    }
}; 