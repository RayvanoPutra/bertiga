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
            $table->string('no_rekening');
            $table->foreign('no_rekening')->references('no_rekening')->on('nasabah')
                ->onDelete('cascade');
            $table->foreignId('petugas_id')->nullable()->constrained('petugas');
            $table->foreignId('jenis_transaksi_id')->constrained('jenis_transaksi');

            //Data Transaksi
            $table->timestamp('tgl_transaksi')->nullable();
            $table->bigInteger('jumlah');

            // Alur 
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('keterangan_nasabah')->nullable();

            // Data Historis untuk Buku Tabungan
            $table->string('nama_saat_transaksi');
            $table->string('kelas_saat_transaksi')->nullable(); 
            $table->string('jurusan_saat_transaksi')->nullable();
            $table->bigInteger('saldo_sebelum');
            $table->bigInteger('saldo_setelah');
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
