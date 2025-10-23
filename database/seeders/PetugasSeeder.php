<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Petugas;
use Illuminate\Support\Facades\Hash;

class PetugasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Petugas::updateOrCreate(
            [
                'username' => 'admin' // Kolom unik untuk dicek
            ],
            [
                'nama' => 'Admin Bank Mini', // Data yang akan di-insert atau di-update
                'password' => Hash::make('password')
            ]
        );
    }
}
