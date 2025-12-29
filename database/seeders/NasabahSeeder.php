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
            ['no_induk' => '1002'], // UBAH: Gunakan no_induk sebagai pengenal unik
            [
                'no_rekening' => '987654321',
                'kode_kelas' => 'X-RPL-1-2425', 
                'nama' => 'Riaann',
                'email' => 'adriandzariatmaulana@gmail.com',
                'jenis_rekening' => 'siswa',
                'saldo' => 50000,
                'password' => Hash::make('Rian123'),
                'status' => 'aktif', // Tambahkan status jika kolom status sudah ada
            ]
        );

        // 2. Nasabah Budi (Baru)
        Nasabah::updateOrCreate(
            ['no_induk' => '2025001'], // UBAH: Gunakan no_induk
            [
                'no_rekening' => '123123123',
                'kode_kelas' => 'XI-TKJ-2-2425', 
                'nama' => 'Budi Santoso',
                'email' => 'adriancogans@gmail.com',
                'no_telp' => '081234567890',
                'jenis_rekening' => 'siswa',
                'saldo' => 0, 
                'password' => Hash::make('budi123'),
                'status' => 'aktif',
            ]
        );

        // 3. Nasabah Siti (Guru)
        Nasabah::updateOrCreate(
            ['no_induk' => '19850101'], // UBAH: Gunakan no_induk
            [
                'no_rekening' => '999888777',
                'kode_kelas' => null, 
                'nama' => 'Siti Aminah, S.Pd.',
                'email' => 'siti@guru.sch.id',
                'no_telp' => '081299988877', 
                'jenis_rekening' => 'guru',
                'saldo' => 1000000,
                'password' => Hash::make('guru123'),
                'status' => 'aktif',
            ]
        );
    }
}