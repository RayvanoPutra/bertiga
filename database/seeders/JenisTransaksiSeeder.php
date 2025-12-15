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
        JenisTransaksi::create(['kode_jenis' => 'SETOR', 'nama_jenis' => 'Setor Tunai']);
        JenisTransaksi::create(['kode_jenis' => 'TARIK', 'nama_jenis' => 'Tarik Tunai']);
        JenisTransaksi::create(['kode_jenis' => 'AWAL', 'nama_jenis' => 'Saldo Awal']);
    }
}