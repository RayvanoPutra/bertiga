<?php

// Buat file baru bernama NasabahSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nasabah; // Pastikan model Nasabah sudah dibuat
use Illuminate\Support\Facades\Hash;

class NasabahSeeder extends Seeder
{
    public function run(): void
    {
        Nasabah::updateOrCreate(
    ['username' => 'Rian'], // Kunci unik
    [
        'no_rekening' => '987654321', // <-- GANTI JADI BARU
        'kelas_id' => 1,
        'no_induk' => '1002', // <-- GANTI JADI BARU
        'nama' => 'Riaann',
        'email' => 'rian@email.com',
        'jenis_rekening' => 'siswa',
        'saldo' => 50000,
        'password' => Hash::make('Rian123'),
    ]
);

Nasabah::updateOrCreate(
            ['username' => 'budi'], // Username login
            [
                'no_rekening' => '123123123', // No Rekening unik
                'kelas_id' => 2, // Pastikan kelas ID 2 ada (misal: XI TKJ 1)
                'no_induk' => '2025001', // NIS unik
                'nama' => 'Budi Santoso',
                'email' => 'budi@sekolah.sch.id',
                'no_telp' => '081234567890',
                'alamat' => 'Jl. Merdeka No. 45, Jakarta',
                'jenis_rekening' => 'siswa',
                'saldo' => 0, // Saldo awal 0 (biar bisa tes setor pertama & potong admin)
                'password' => Hash::make('budi123'), // Password login
            ]
        );
        
        // Nasabah 3: Siti Aminah (BARU - Guru)
        Nasabah::updateOrCreate(
            ['username' => 'siti_guru'], 
            [
                'no_rekening' => '999888777',
                'kelas_id' => null, // Guru tidak punya kelas
                'no_induk' => '19850101', // NIP
                'nama' => 'Siti Aminah, S.Pd.',
                'email' => 'siti@guru.sch.id',
                'jenis_rekening' => 'guru',
                'saldo' => 1000000, 
                'password' => Hash::make('guru123'), 
            ]
        );
    }
}
