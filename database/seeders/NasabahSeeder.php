<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nasabah;
use Illuminate\Support\Facades\Hash;

class NasabahSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Nasabah Rian
        Nasabah::updateOrCreate(
            ['username' => 'Rian'], 
            [
                'no_rekening' => '987654321',
                // PERBAIKAN: Gunakan kode yang SAMA PERSIS dengan KelasSeeder Anda
                'kode_kelas' => 'X-RPL-1-2425', 
                'no_induk' => '1002',
                'nama' => 'Riaann',
                'email' => 'adriandzariatmaulana@gmail.com',
                'jenis_rekening' => 'siswa',
                'saldo' => 50000,
                'password' => Hash::make('Rian123'),
            ]
        );

        // 2. Nasabah Budi (Baru)
        Nasabah::updateOrCreate(
            ['username' => 'budi'],
            [
                'no_rekening' => '123123123',
                // PERBAIKAN: Gunakan kode yang SAMA PERSIS dengan KelasSeeder Anda
                'kode_kelas' => 'XI-TKJ-2-2425', 
                'no_induk' => '2025001',
                'nama' => 'Budi Santoso',
                'email' => 'adriancogans@gmail.com',
                'no_telp' => '081234567890',
                'jenis_rekening' => 'siswa',
                'saldo' => 0, 
                'password' => Hash::make('budi123'),
            ]
        );

        // 3. Nasabah Siti (Guru) - Tidak perlu diubah karena kode_kelas null
        Nasabah::updateOrCreate(
            ['username' => 'siti_guru'],
            [
                'no_rekening' => '999888777',
                'kode_kelas' => null, 
                'no_induk' => '19850101',
                'nama' => 'Siti Aminah, S.Pd.',
                'email' => 'siti@guru.sch.id',
                'no_telp' => '081299988877', 
                'jenis_rekening' => 'guru',
                'saldo' => 1000000,
                'password' => Hash::make('guru123'),
            ]
        );
    }
}