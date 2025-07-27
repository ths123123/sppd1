<?php
// File: database/migrations/2025_06_24_151932_create_approvals_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('users')->onDelete('cascade');

            $table->integer('level'); // 1=Kasubbag, 2=Sekretaris, 3=ppk
            $table->enum('role', ['kasubbag', 'sekretaris', 'ppk']);

            $table->enum('status', [
                'pending', 'approved', 'rejected',
                'revision_minor', 'revision_major'
            ])->default('pending');

            $table->text('comments')->nullable();
            $table->json('revision_notes')->nullable(); // untuk detail revisi

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['travel_request_id', 'level']);
            $table->index(['approver_id', 'status']);
            $table->unique(['travel_request_id', 'level']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('approvals');
    }
};
