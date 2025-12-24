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
            // Format: TR-20250921-0001
            $table->string('kode_transaksi', 50)->primary();
            // Relasi Nasabah
            $table->string('no_rekening', 20);
            $table->foreign('no_rekening')->references('no_rekening')->on('nasabah')
                ->onDelete('cascade');
            // Relasi Petugas
            $table->string('kode_petugas', 20)->nullable();
            $table->foreign('kode_petugas')->references('kode_petugas')->on('petugas');
            // Relasi Jenis Transaksi
            $table->string('kode_jenis', 10);
            $table->foreign('kode_jenis')->references('kode_jenis')->on('jenis_transaksi');

            //Data Transaksi
            $table->timestamp('tgl_transaksi')->nullable();
            $table->bigInteger('jumlah');

            // Alur 
            $table->enum('status', ['pending', 'success', 'rejected'])->default('pending');
            $table->text('keterangan_nasabah')->nullable();
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