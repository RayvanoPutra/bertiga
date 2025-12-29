<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Data Master (Induk)
            JurusanSeeder::class,
            TahunAjaranSeeder::class,
            JenisTransaksiSeeder::class,
            PetugasSeeder::class,
            PengaturanSeeder::class, // (Jika ada)

            // Data Kelas (Wajib sebelum Nasabah)
            KelasSeeder::class,

            // --- PASTIKAN BARIS INI ADA & TIDAK DIKOMENTARI ---
            NasabahSeeder::class, 
        ]);
    }
}