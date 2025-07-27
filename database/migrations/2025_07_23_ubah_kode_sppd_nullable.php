<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('travel_requests', function (Blueprint $table) {
            $table->string('kode_sppd', 50)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('travel_requests', function (Blueprint $table) {
            $table->string('kode_sppd', 50)->nullable(false)->change();
        });
    }
}; 