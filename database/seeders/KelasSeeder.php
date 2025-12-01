<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas; // Pastikan model Kelas ada

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * PENTING:
         * Pastikan di tabel 'tahun_ajaran' SUDAH ADA data dengan id=1
         * Pastikan di tabel 'jurusan' SUDAH ADA data dengan kode_jurusan='RPL'
         * (Data ini dibuat oleh JurusanSeeder dan TahunAjaranSeeder Anda)
         */
        Kelas::updateOrCreate(
            ['id' => 1], // Kunci untuk NasabahSeeder
            [
                'nama_kelas' => 'X RPL 1',
                'tahun_ajaran_id' => 1,  // <-- Sesuaikan jika ID-nya beda
                'kode_jurusan' => 'RPL' // <-- Ganti ini jika kode jurusan Anda beda
            ]
        );

        // Tambahkan kelas lain jika perlu
        Kelas::updateOrCreate(
            ['id' => 2], 
            [
                'nama_kelas' => 'X TKJ 1',
                'tahun_ajaran_id' => 1,  
                'kode_jurusan' => 'TKJ' // Pastikan 'TKJ' ada di tabel jurusan
            ]
        );
    }
}