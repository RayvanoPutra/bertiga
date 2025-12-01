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
}
