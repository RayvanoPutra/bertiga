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
<<<<<<< HEAD
            //Format: X-RPL-1-2425 (Kelas 10 RPL 1 Tahun Ajaran 2024/2025)
            $table->string('kode_kelas', 20)->primary();
            $table->string('nama_kelas', 50);

            $table->string('kode_tahun_ajaran', 20);
            $table->string('kode_jurusan', 20);

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

            $table->unique(['nama_kelas', 'kode_tahun_ajaran', 'kode_jurusan'], 'kelas_unique_combo');  

=======
            $table->id();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')
                ->onDelete('cascade');
            $table->string('kode_jurusan');
            $table->foreign('kode_jurusan')->references('kode_jurusan')->on('jurusan')
                ->onDelete('cascade');
            $table->string('nama_kelas');
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
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
