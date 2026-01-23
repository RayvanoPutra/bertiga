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
<<<<<<< HEAD
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
=======
            $table->id();
            $table->string('no_rekening');
            $table->foreign('no_rekening')->references('no_rekening')->on('nasabah')
                ->onDelete('cascade');
            $table->foreignId('petugas_id')->nullable()->constrained('petugas');
            $table->foreignId('jenis_transaksi_id')->constrained('jenis_transaksi');
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740

            //Data Transaksi
            $table->timestamp('tgl_transaksi')->nullable();
            $table->bigInteger('jumlah');

            // Alur 
<<<<<<< HEAD
            $table->enum('status', ['pending', 'success', 'rejected'])->default('pending');
            $table->text('keterangan_nasabah')->nullable();
=======
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('keterangan_nasabah')->nullable();

            // Data Historis untuk Buku Tabungan
            $table->string('nama_saat_transaksi');
            $table->string('kelas_saat_transaksi')->nullable(); 
            $table->string('jurusan_saat_transaksi')->nullable();
            $table->bigInteger('saldo_sebelum');
            $table->bigInteger('saldo_setelah');
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
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
<<<<<<< HEAD
};
=======
};
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
