<?php
// File: database/migrations/2025_06_24_151931_create_travel_requests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('travel_requests', function (Blueprint $table) {
            $table->id();
            $table->string('kode_sppd', 50)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Data Perjalanan
            $table->string('tujuan');
            $table->text('keperluan');
            $table->date('tanggal_berangkat');
            $table->date('tanggal_kembali');
            $table->integer('lama_perjalanan'); // dalam hari
            $table->string('transportasi');
            $table->string('tempat_menginap')->nullable();

            // Data Anggaran
            $table->decimal('biaya_transport', 15, 2)->default(0);
            $table->decimal('biaya_penginapan', 15, 2)->default(0);
            $table->decimal('uang_harian', 15, 2)->default(0);
            $table->decimal('biaya_lainnya', 15, 2)->default(0);
            $table->decimal('total_biaya', 15, 2)->default(0);
            $table->string('sumber_dana')->nullable();

            // Status dan Workflow
            $table->enum('status', [
                'in_review', 'revision', 'rejected', 'completed'
            ])->default('in_review');

            $table->integer('current_approval_level')->default(0);
            $table->json('approval_history')->nullable();

            // Metadata
            $table->text('catatan_pemohon')->nullable();
            $table->text('catatan_approval')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->string('nomor_surat_tugas')->nullable();
            $table->date('tanggal_surat_tugas')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['status', 'current_approval_level']);
            $table->index('tanggal_berangkat');
        });
    }

    public function down()
    {
        Schema::dropIfExists('travel_requests');
    }
};
