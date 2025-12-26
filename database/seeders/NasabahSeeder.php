<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nasabah;
use Illuminate\Support\Facades\Hash;

class NasabahSeeder extends Seeder
{
    public function run(): void
    {
        //   KOSONGKAN JIKA SUDAH TIDAK DIPERLUKAN
        //   Data nasabah sebaiknya diinput manual via Web Petugas
        
        //   1. Nasabah Rian
        //  *
        //  Nasabah::updateOrCreate(
        //      ['no_rekening' => '987654321'], 
        //      [
        //          'kode_kelas' => 'X-RPL-1-2425', 
        //          'no_induk' => '1002',
        //          'nama' => 'Riaann',
        //          'email' => 'adriandzariatmaulana@gmail.com',
        //          'jenis_rekening' => 'siswa',
        //          'saldo' => 50000,
        //          'password' => Hash::make('Rian123'),
        //          'status' => 'aktif',
        //      ]
        //  );
        //  *

        //   ... (Kode lainnya dikomentari agar tidak jalan)
    }
}