<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travel_requests', function (Blueprint $table) {
            $table->string('tempat_berangkat')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('travel_requests', function (Blueprint $table) {
            $table->dropColumn('tempat_berangkat');
        });
    }
}; 