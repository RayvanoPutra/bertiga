<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JenisTransaksi;

class JenisTransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
<<<<<<< HEAD
        JenisTransaksi::create(['kode_jenis' => 'SETOR', 'nama_jenis' => 'Setor Tunai']);
        JenisTransaksi::create(['kode_jenis' => 'TARIK', 'nama_jenis' => 'Tarik Tunai']);
        JenisTransaksi::create(['kode_jenis' => 'AWAL', 'nama_jenis' => 'Saldo Awal']);
=======
        JenisTransaksi::updateOrCreate(['nama_jenis' => 'Setor Tunai']);
        JenisTransaksi::updateOrCreate(['nama_jenis' => 'Tarik Tunai']);
        JenisTransaksi::updateOrCreate(['nama_jenis' => 'Saldo Awal']);
        JenisTransaksi::updateOrCreate(['nama_jenis' => 'Biaya Admin']);
        JenisTransaksi::updateOrCreate(['nama_jenis' => 'Bunga Bank']);
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
    }
}
