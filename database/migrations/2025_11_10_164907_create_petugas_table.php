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
        Schema::create('petugas', function (Blueprint $table) {
<<<<<<< HEAD
            $table->string('kode_petugas', 20)->primary();
            $table->string('nama_petugas', 100);
            $table->string('username', 50)->unique();
            $table->string('password');
=======
            $table->id();
            $table->string('nama_petugas');
            $table->string('username')->unique();
            $table->string('password'); // Akan diHash
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
            $table->enum('role', ['superadmin', 'admin']);
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
