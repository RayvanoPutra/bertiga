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
            $table->id();

            // Relasi ke tabel jurusan
            $table->foreignId('jurusan_id')->constrained('jurusan')
                ->onUpdate('cascade')->onDelete('restrict');

            // Relasi ke tabel tahun_ajaran
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->string('nama_kelas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petugas');
    }
};
