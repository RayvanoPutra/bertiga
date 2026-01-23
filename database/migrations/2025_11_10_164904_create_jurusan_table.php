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
        Schema::create('jurusan', function (Blueprint $table) {
<<<<<<< HEAD
            // Hanya kode dan nama sesuai permintaan Anda
            $table->string('kode_jurusan', 20)->primary();
            $table->string('nama_jurusan', 100);
            
            // HAPUS bagian kode_tahun_ajaran dan Foreign Key-nya di sini
            
=======
            $table->string('kode_jurusan')->primary();
            $table->string('nama_jurusan');
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurusan');
    }
<<<<<<< HEAD
};
=======
};
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
