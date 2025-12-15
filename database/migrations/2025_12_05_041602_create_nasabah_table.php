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
        // Kolom utama
        $table->string('no_rekening')->primary();

        // Relasi dengan Kelas
        $table->string('kode_kelas')->nullable(); // Pastikan tipe data kode_kelas sesuai dengan tabel kelas
        $table->foreign('kode_kelas')->references('kode_kelas')->on('kelas')->onDelete('cascade'); // Menghapus data nasabah jika kelas dihapus

        // Kolom informasi nasabah
        $table->string('no_induk')->unique();
        $table->string('nama');
        $table->string('email')->nullable();
        $table->string('no_telp')->nullable();
        $table->enum('jenis_rekening', ['siswa', 'guru']);
        $table->bigInteger('saldo')->default(0); // Saldo defaultnya 0

        // Info Login Nasabah
        $table->string('username')->unique();
        $table->string('password');

        // Kolom timestamps
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
};