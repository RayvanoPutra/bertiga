<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pengaturan;

class PengaturanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Masukkan pengaturan biaya admin
        // Sesuaikan nama kolom dengan migrasi Anda ('nama_pengaturan', 'nilai')
        Pengaturan::updateOrCreate(
            ['nama_pengaturan' => 'biaya_admin'], 
            ['nilai' => '12000']
        );
        
        // Pengaturan lain (opsional)
        Pengaturan::updateOrCreate(
            ['nama_pengaturan' => 'biaya_admin_daftar'], 
            ['nilai' => '12000']
        );
    }
}