<?php
// File: database/migrations/2025_06_24_151932_create_documents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');

            $table->string('filename');
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('file_type'); // pdf, jpg, png, doc, etc
            $table->integer('file_size'); // in bytes
            $table->string('mime_type');

            $table->enum('document_type', [
                'supporting', // dokumen pendukung
                'proof', // bukti perjalanan
                'receipt', // kwitansi
                'photo', // foto kegiatan
                'report', // laporan perjalanan
                'generated_pdf' // PDF SPPD yang di-generate
            ]);

            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');

            $table->timestamps();

            // Indexes
            $table->index(['travel_request_id', 'document_type']);
            $table->index('uploaded_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
};
