<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TahunAjaran;

class TahunAjaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
<<<<<<< HEAD
        TahunAjaran::create([
            'kode_tahun_ajaran' => 'TA-2425',
            'tahun_ajaran' => '2024/2025', 
            'status' => 'aktif'
        ]);

        TahunAjaran::create([
            'kode_tahun_ajaran' => 'TA-2324',
            'tahun_ajaran' => '2023/2024',
            'status' => 'nonaktif'
        ]);
=======
        TahunAjaran::updateOrCreate(
            ['tahun_ajaran' => '2024/2025'],
            ['status' => 'aktif']
        );

        TahunAjaran::updateOrCreate(
            ['tahun_ajaran' => '2025/2026'],
            ['status' => 'aktif']
        );
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
    }
}
