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
        Schema::create('template_dokumens', function (Blueprint $table) {
            $table->id();
            $table->string('nama_template');
            $table->string('path_file');
            $table->enum('tipe_file', ['docx', 'pdf']);
            $table->boolean('status_aktif')->default(false);
            $table->enum('jenis_template', ['spd', 'sppd', 'laporan_akhir']);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_dokumens');
    }
};
