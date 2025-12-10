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
            $table->string('no_rekening', 20)->primary();
            //Relasi Kelas
            $table->string('kode_kelas', 20)->nullable();
            $table->foreign('kode_kelas')->references('kode_kelas')->on('kelas')->onDelete('cascade');

            $table->string('no_induk', 20)->unique();
            $table->string('nama', 100);
            $table->string('email', 100)->nullable();
            $table->string('no_telp', 20)->nullable();
            $table->enum('jenis_rekening', ['siswa', 'guru']);
            $table->bigInteger('saldo')->default(0);

            // Info Login Nasabah
            $table->string('username', 50)->unique();
            $table->string('password');
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
