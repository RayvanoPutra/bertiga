<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kelas;
class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    // Cukup buat kelasnya saja, jangan ada Jurusan::create di sini
    \App\Models\Kelas::create([
        'kode_kelas' => 'X-RPL-1-2425',
        'nama_kelas' => 'X RPL 1',
        'kode_tahun_ajaran' => 'TA-2425',
        'kode_jurusan' => 'RPL'
    ]);

    \App\Models\Kelas::create([
        'kode_kelas' => 'XI-TKJ-2-2425',
        'nama_kelas' => 'XI TKJ 2',
        'kode_tahun_ajaran' => 'TA-2425',
        'kode_jurusan' => 'TKJ' // Sekarang aman karena TKJ sudah ada di JurusanSeeder
    ]);
}
}
