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
        // Add missing columns for documents table
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('documents', 'jenis_dokumen')) {
                $table->enum('jenis_dokumen', ['surat_tugas', 'surat_izin', 'laporan'])->nullable();
            }
            if (!Schema::hasColumn('documents', 'nama_dokumen')) {
                $table->string('nama_dokumen')->nullable();
            }
        });

        // Add missing columns for settings table
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'is_public')) {
                $table->boolean('is_public')->default(true);
            }
            if (!Schema::hasColumn('settings', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('settings', 'type')) {
                $table->string('type')->default('string');
            }
        });

        // Add missing columns for template_dokumens table
        Schema::table('template_dokumens', function (Blueprint $table) {
            if (!Schema::hasColumn('template_dokumens', 'jenis_dokumen')) {
                $table->enum('jenis_dokumen', ['sppd', 'surat_tugas', 'surat_izin', 'laporan'])->nullable();
            }
            if (!Schema::hasColumn('template_dokumens', 'deskripsi')) {
                $table->text('deskripsi')->nullable();
            }
            if (!Schema::hasColumn('template_dokumens', 'file_path')) {
                $table->string('file_path')->nullable();
            }
            if (!Schema::hasColumn('template_dokumens', 'file_size')) {
                $table->integer('file_size')->nullable();
            }
            if (!Schema::hasColumn('template_dokumens', 'mime_type')) {
                $table->string('mime_type')->nullable();
            }
            if (!Schema::hasColumn('template_dokumens', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'jenis_dokumen', 'nama_dokumen']);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['is_public', 'description', 'type']);
        });

        Schema::table('template_dokumens', function (Blueprint $table) {
            $table->dropColumn(['jenis_dokumen', 'deskripsi', 'file_path', 'file_size', 'mime_type', 'is_active']);
        });
    }
};
