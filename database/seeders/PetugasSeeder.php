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
        Petugas::create([
            'kode_petugas' => 'SADM001',
            'nama_petugas' => 'superadmin',
            'username' => 'admin',
            'password' => Hash::make('password'), // Password: password
            'role' => 'superadmin'
        ]);

        Petugas::create([
            'kode_petugas' => 'ADM001',
            'nama_petugas' => 'admin',
            'username' => 'petugas',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);
    }
}