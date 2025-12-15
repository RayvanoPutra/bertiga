<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nasabah;      // Wajib di-import
use App\Models\Transaksi;    // Wajib di-import
use Carbon\Carbon;           // Wajib di-import untuk perhitungan tanggal

class DashboardController extends Controller
{
    public function getMetrics()
    {
        // Mendapatkan tanggal hari ini
        $today = Carbon::today();

        // 1. Jumlah Nasabah
        $jumlahNasabah = Nasabah::count();

        // 2. Total Tabungan (Total Saldo dari semua Nasabah)
        // Pastikan kolom 'saldo' di tabel nasabah bertipe numerik (int/bigint)
        $totalTabungan = Nasabah::sum('saldo');

        // 3. Jumlah Transaksi (Hanya Transaksi Hari Ini)
        $jumlahTransaksiHariIni = Transaksi::whereDate('tgl_transaksi', $today)->count();
        
        // 4. Tabungan Hari Ini (Neto: Total Setor dikurangi Total Tarik Hari Ini)
        $setorHariIni = Transaksi::where('kode_jenis', 'SETOR')
                                 ->whereDate('tgl_transaksi', $today)
                                 ->sum('jumlah');
        
        $tarikHariIni = Transaksi::where('kode_jenis', 'TARIK')
                                 ->whereDate('tgl_transaksi', $today)
                                 ->sum('jumlah');
        
        $tabunganHariIni = $setorHariIni - $tarikHariIni;

        // Mengirim semua metrik dalam satu respon JSON
        return response()->json([
            'status' => 'success',
            'data' => [
                'jumlah_nasabah' => (int) $jumlahNasabah, // Total Nasabah
                'total_tabungan' => (int) $totalTabungan, // Total Saldo Bank
                'tabungan_hari_ini' => (int) $tabunganHariIni, // Setor - Tarik hari ini
                'jumlah_transaksi' => (int) $jumlahTransaksiHariIni, // Count Transaksi hari ini
            ]
        ]);
    }
}