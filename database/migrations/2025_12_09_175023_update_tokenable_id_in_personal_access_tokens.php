<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Ubah tokenable_id menjadi string
            $table->string('tokenable_id')->change();  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Kembalikan ke tipe integer jika diperlukan
            $table->unsignedBigInteger('tokenable_id')->change();
        });
    }
};
