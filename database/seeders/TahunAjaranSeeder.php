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
        TahunAjaran::updateOrCreate(
            ['tahun_ajaran' => '2024/2025'],
            ['status' => 'aktif']
        );

        TahunAjaran::updateOrCreate(
            ['tahun_ajaran' => '2025/2026'],
            ['status' => 'aktif']
        );
    }
}
