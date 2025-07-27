<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop existing unique constraint if exists
            try {
                $table->dropUnique(['nip']);
            } catch (\Exception $e) {
                // Constraint doesn't exist, continue
            }
            
            // Change NIP field length from 50 to 18 to match Indonesian government standard
            $table->string('nip', 18)->nullable()->change();
            
            // Add unique constraint back
            $table->unique('nip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop unique constraint
            try {
                $table->dropUnique(['nip']);
            } catch (\Exception $e) {
                // Constraint doesn't exist, continue
            }
            
            // Revert back to original length
            $table->string('nip', 50)->nullable()->change();
            
            // Add unique constraint back
            $table->unique('nip');
        });
    }
};
