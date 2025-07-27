<?php
// File: database/migrations/2025_06_24_151935_create_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->string('group')->default('general'); // general, approval, notification, etc
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_editable')->default(true);
            $table->timestamps();

            // Index
            $table->index(['group', 'key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
