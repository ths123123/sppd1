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
        // Drop the existing enum constraint and recreate it with new values
        Schema::table('notifications', function (Blueprint $table) {
            // Drop the existing enum constraint
            $table->dropColumn('type');
        });

        Schema::table('notifications', function (Blueprint $table) {
            // Recreate the enum with all required values
            $table->enum('type', [
                'info', 'success', 'warning', 'error',
                'approval_request', 'status_update', 'reminder',
                'sppd_submitted', 'sppd_submitted_confirmation', 'sppd_completed', 
                'sppd_rejected', 'sppd_revision'
            ])->after('travel_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('type', [
                'info', 'success', 'warning', 'error',
                'approval_request', 'status_update', 'reminder'
            ])->after('travel_request_id');
        });
    }
};
