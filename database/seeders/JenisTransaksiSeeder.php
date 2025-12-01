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
        JenisTransaksi::updateOrCreate(['nama_jenis' => 'Setor Tunai']);
        JenisTransaksi::updateOrCreate(['nama_jenis' => 'Tarik Tunai']);
        JenisTransaksi::updateOrCreate(['nama_jenis' => 'Saldo Awal']);
        JenisTransaksi::updateOrCreate(['nama_jenis' => 'Biaya Admin']);
        JenisTransaksi::updateOrCreate(['nama_jenis' => 'Bunga Bank']);
    }
}
