<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengaturan; // Pastikan Anda memiliki model Pengaturan
use Illuminate\Support\Facades\DB;

class PengaturanController extends Controller
{
    /**
     * Mengambil nilai biaya admin pendaftaran dari database.
     * Menggunakan nilai default Rp 12.000 jika tidak ditemukan di pengaturan.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBiayaAdminDaftar()
    {
        // 1. Ambil nilai biaya admin dari tabel 'pengaturan'
        // Asumsi: kolom 'nama_pengaturan' berisi 'biaya_admin_daftar'
        $settingAdmin = Pengaturan::where('nama_pengaturan', 'biaya_admin_daftar')->first();
        
        // 2. Tentukan nilai biaya admin (Raw Integer)
        // Default 12000 jika pengaturan tidak ada
        $biayaAdminRaw = $settingAdmin ? (int)$settingAdmin->nilai : 12000;
        
        // 3. Format nilai untuk tampilan di frontend
        $biayaAdminFormatted = number_format($biayaAdminRaw, 0, ',', '.');
        
        return response()->json([
            'status' => 'success',
            // Nilai mentah (raw) untuk perhitungan JS
            'biaya_admin_raw' => $biayaAdminRaw, 
            // Nilai yang sudah diformat untuk ditampilkan
            'biaya_admin_formatted' => $biayaAdminFormatted, 
        ]);
    }
}