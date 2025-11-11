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
            ['username' => 'superadmin'], // Kunci unik untuk dicek
            [
                'nama_petugas' => 'Super Admin',
                'password' => Hash::make('password'), // Password-nya adalah 'password'
                'role' => 'superadmin'
            ]
        );

        Petugas::updateOrCreate(
            ['username' => 'admin'], // Kunci unik untuk dicek
            [
                'nama_petugas' => 'Admin Biasa',
                'password' => Hash::make('password'), // Password-nya adalah 'password'
                'role' => 'admin'
            ]
        );
    }
}
