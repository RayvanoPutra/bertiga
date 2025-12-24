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
        Schema::create('jurusan', function (Blueprint $table) {
            // Hanya kode dan nama sesuai permintaan Anda
            $table->string('kode_jurusan', 20)->primary();
            $table->string('nama_jurusan', 100);
            
            // HAPUS bagian kode_tahun_ajaran dan Foreign Key-nya di sini
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurusan');
    }
};