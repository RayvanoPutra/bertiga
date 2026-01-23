<?php

namespace Database\Seeders;

<<<<<<< HEAD
=======
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
<<<<<<< HEAD
        $this->call([
            // Data Master (Induk)
            TahunAjaranSeeder::class,
            JurusanSeeder::class,
            KelasSeeder::class,
            JenisTransaksiSeeder::class,
            PetugasSeeder::class,
            PengaturanSeeder::class, // (Jika ada)

            // Data Kelas (Wajib sebelum Nasabah)
            

            // --- PASTIKAN BARIS INI ADA & TIDAK DIKOMENTARI ---
            NasabahSeeder::class, 
        ]);
    }
}
=======
        // User::factory(10)->create();

        $this->call([
            JurusanSeeder::class,
            TahunAjaranSeeder::class,
            JenisTransaksiSeeder::class,
            PetugasSeeder::class,
        ]);

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
