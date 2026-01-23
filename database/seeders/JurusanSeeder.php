<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Jurusan;

class JurusanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
<<<<<<< HEAD
{
    // Pastikan kode_tahun_ajaran pakai strip '-' (TA-2425) agar sinkron
    \App\Models\Jurusan::create([
        'kode_jurusan' => 'RPL',
        'nama_jurusan' => 'Rekayasa Perangkat Lunak',
    ]);

    \App\Models\Jurusan::create([
        'kode_jurusan' => 'AKL',
        'nama_jurusan' => 'Akuntansi Keuangan dan Lembaga',
    ]);

    // TAMBAHKAN INI AGAR TKJ TERDAFTAR
    \App\Models\Jurusan::create([
        'kode_jurusan' => 'TKJ',
        'nama_jurusan' => 'Teknik Komputer dan Jaringan',
    ]);
}
=======
    {
        Jurusan::updateOrCreate(
            ['kode_jurusan' => 'RPL'],
            ['nama_jurusan' => 'Rekayasa Perangkat Lunak']
        );

        Jurusan::updateOrCreate(
            ['kode_jurusan' => 'TKJ'],
            ['nama_jurusan' => 'Teknik Komputer dan Jaringan']
        );
        Jurusan::updateOrCreate(
            ['kode_jurusan' => 'AKT'],
            ['nama_jurusan' => 'Akuntansi']
        );

        Jurusan::updateOrCreate(
            ['kode_jurusan' => 'GURU'],
            ['nama_jurusan' => 'Staf Guru & Pengajar']
        );
    }
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
}
