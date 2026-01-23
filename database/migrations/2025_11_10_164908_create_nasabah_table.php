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
        Schema::create('nasabah', function (Blueprint $table) {
<<<<<<< HEAD
            // Kolom utama
            $table->string('no_rekening', 20)->primary();

            // Relasi dengan Kelas
            $table->string('kode_kelas', 20)->nullable();
            $table->foreign('kode_kelas')->references('kode_kelas')->on('kelas')->onDelete('cascade');

            // Kolom informasi nasabah
            $table->string('no_induk', 20)->unique();
            $table->string('nama', 100);
            
            $table->string('email', 100)->nullable(); 
            
            $table->string('no_telp', 20)->nullable();
            $table->enum('jenis_rekening', ['siswa', 'guru']);
            $table->bigInteger('saldo')->default(0);

            // Info Login Nasabah
            $table->string('password');
            
            // --- HAPUS KOLOM STATUS DARI SINI ---
            // Kolom 'status' akan ditambahkan lewat migrasi terpisah:
            // 2025_12_12_004324_add_status_to_nasabah_table.php
            // ------------------------------------

=======
            $table->string('no_rekening')->primary();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas');
            $table->string('no_induk')->unique();
            $table->string('nama');
            $table->string('email')->nullable();
            $table->string('no_telp')->nullable();
            $table->text('alamat')->nullable();
            $table->enum('jenis_rekening', ['siswa', 'guru']);
            $table->bigInteger('saldo')->default(0);

            // Info Login Nasabah (untuk Android)
            $table->string('username')->unique();
            $table->string('password');
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nasabah');
    }
<<<<<<< HEAD
};
=======
};
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
