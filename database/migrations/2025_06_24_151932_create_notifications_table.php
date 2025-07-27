<?php
// File: database/migrations/2025_06_24_151932_create_notifications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('travel_request_id')->nullable()->constrained()->onDelete('cascade');

            $table->string('title');
            $table->text('message');
            $table->enum('type', [
                'info', 'success', 'warning', 'error',
                'approval_request', 'status_update', 'reminder',
                'sppd_submitted', 'sppd_submitted_confirmation', 'sppd_completed', 
                'sppd_rejected', 'sppd_revision'
            ]);

            $table->json('data')->nullable(); // additional data
            $table->string('action_url')->nullable(); // link untuk action
            $table->string('action_text')->nullable(); // text untuk button action

            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_important')->default(false);

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'type']);
            $table->index('travel_request_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
