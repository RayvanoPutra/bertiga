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
    $data = [
        ['kode_jenis' => 'SETOR', 'nama_jenis' => 'Setor Tunai'],
        ['kode_jenis' => 'TARIK', 'nama_jenis' => 'Tarik Tunai'],
        ['kode_jenis' => 'AWAL', 'nama_jenis' => 'Saldo Awal'],
        ['kode_jenis' => 'ADM', 'nama_jenis' => 'Biaya Administrasi'],
    ];

    foreach ($data as $val) {
        \App\Models\JenisTransaksi::updateOrCreate(
            ['kode_jenis' => $val['kode_jenis']], // Kunci pencarian
            ['nama_jenis' => $val['nama_jenis']]  // Data yang diupdate/tambah
        );
    }
}
}
