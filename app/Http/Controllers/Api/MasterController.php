<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengaturan; // Pastikan model Pengaturan diimpor

class MasterController extends Controller
{
    /**
     * Mengambil nilai biaya admin pendaftaran dari tabel pengaturan.
     */
    public function getBiayaAdmin(Request $request)
    {
        // Cari pengaturan dengan kunci yang disepakati
        $setting = Pengaturan::where('nama_pengaturan', 'biaya_admin_daftar')->first();
        
        // Mengembalikan nilai sebagai integer, default 0 jika tidak ditemukan
        return response()->json([
            'nilai' => $setting ? (int)$setting->nilai : 0,
            'message' => 'Biaya admin berhasil dimuat.'
        ]);
    }
    
    // Jika Anda memiliki MasterDataController terpisah, fungsi lain tidak perlu di sini.
}