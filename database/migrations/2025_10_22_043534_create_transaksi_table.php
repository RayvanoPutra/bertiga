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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();

            // Relasi ke tabel siswa
            $table->foreignId('siswa_id')->constrained('siswa')
                ->onUpdate('cascade')->onDelete('cascade'); // Jika siswa dihapus, riwayatnya ikut terhapus

            // Relasi ke tabel petugas
            $table->foreignId('petugas_id')->constrained('petugas')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->enum('jenis', ['setor', 'tarik', 'saldo_awal']);
            $table->integer('jumlah');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
