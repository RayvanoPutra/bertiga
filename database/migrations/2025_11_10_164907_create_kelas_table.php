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
        Schema::create('kelas', function (Blueprint $table) {
            //Format: X-RPL-1-2425 (Kelas 10 RPL 1 Tahun Ajaran 2024/2025)
            $table->string('kode_kelas')->primary();
            $table->string('nama_kelas');

            $table->string('kode_tahun_ajaran');
            $table->string('kode_jurusan');

            // Relasi Tahun Ajaran
            $table->foreign('kode_tahun_ajaran')
                ->references('kode_tahun_ajaran') 
                ->on('tahun_ajaran')            
                ->onDelete('cascade');

            // Relasi jurusan
            $table->foreign('kode_jurusan')
                ->references('kode_jurusan')    
                ->on('jurusan')                 
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
