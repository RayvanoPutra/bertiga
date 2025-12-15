<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Pengaturan;

class PengaturanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data default pengaturan
        $settings = [
            [
                'nama_pengaturan' => 'biaya_admin_daftar',
                'nilai' => '12000', // Biaya admin default Rp 12.000
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Anda bisa tambahkan pengaturan lain di sini (misalnya: min_setoran_awal, dll.)
        ];

        // Memastikan tidak ada duplikasi sebelum insert
        foreach ($settings as $setting) {
            Pengaturan::firstOrCreate(
                ['nama_pengaturan' => $setting['nama_pengaturan']],
                ['nilai' => $setting['nilai']]
            );
        }
        $this->call(PengaturanSeeder::class);
    }
}