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
        // PostgreSQL requires a different approach for modifying enum types
        // First, we need to drop the constraint
        DB::statement('ALTER TABLE notifications DROP CONSTRAINT IF EXISTS notifications_type_check');

        // Then create a new constraint with the updated enum values
        DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_type_check 
            CHECK (type::text = ANY (ARRAY[
                'info'::text, 'success'::text, 'warning'::text, 'error'::text,
                'approval_request'::text, 'status_update'::text, 'reminder'::text,
                'sppd_submitted'::text, 'sppd_submitted_confirmation'::text, 'sppd_completed'::text,
                'sppd_rejected'::text, 'sppd_revision'::text, 'sppd_approved'::text
            ]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the previous constraint without 'sppd_approved'
        DB::statement('ALTER TABLE notifications DROP CONSTRAINT IF EXISTS notifications_type_check');
        
        DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_type_check 
            CHECK (type::text = ANY (ARRAY[
                'info'::text, 'success'::text, 'warning'::text, 'error'::text,
                'approval_request'::text, 'status_update'::text, 'reminder'::text,
                'sppd_submitted'::text, 'sppd_submitted_confirmation'::text, 'sppd_completed'::text,
                'sppd_rejected'::text, 'sppd_revision'::text
            ]))");
    }
};
