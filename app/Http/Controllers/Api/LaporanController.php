<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Nasabah;
use App\Models\Pengaturan;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function cetakLaporanAndroid(Request $request) 
    {
        // 1. Ambil nasabah dari token
        $nasabah = $request->user(); 
        if (!$nasabah) return response()->json(['message' => 'Token tidak valid'], 401);
        
        $nasabah->load(['kelas.jurusan']);

        // 2. Ambil parameter bulan
        $bulanMulai = $request->query('bulan_mulai');
        $bulanSelesai = $request->query('bulan_selesai');

        // 3. Ambil data transaksi (Pastikan variabel ini terdefinisi)
        $transaksi = Transaksi::where('no_rekening', $nasabah->no_rekening)
                    ->whereBetween('tgl_transaksi', [
                        $this->getTglAndroid($bulanMulai, 'awal'), 
                        $this->getTglAndroid($bulanSelesai, 'akhir')
                    ])
                    ->orderBy('tgl_transaksi', 'asc')
                    ->get();

        // 4. Bungkus data (Data ini khusus untuk file Blade baru)
        $dataLaporan = [
            'nama_sekolah' => 'BANK MINI SMK YADIKA 2',
            'alamat'       => 'Jl. Raya Kamal No.Kav. 2, Kalideres, Jakarta Barat',
            'nasabah'      => $nasabah,
            'transaksi'    => $transaksi, // Variabel transaksi dikirim ke sini
            'tgl_cetak'    => date('d F Y'),
            'periode'      => $bulanMulai . ' - ' . $bulanSelesai
        ];

        // 5. Panggil file Blade BARU: laporan_android
        $pdf = Pdf::loadView('pdf.laporan_android', $dataLaporan);
        return $pdf->download('Laporan_BankMini_' . $nasabah->nama . '.pdf');
    }

    private function getTglAndroid($bulan, $tipe) {
        $bulanAngka = ['Januari'=>'01','Februari'=>'02','Maret'=>'03','April'=>'04','Mei'=>'05','Juni'=>'06','Juli'=>'07','Agustus'=>'08','September'=>'09','Oktober'=>'10','November'=>'11','Desember'=>'12'];
        $angka = $bulanAngka[$bulan] ?? date('m');
        if($tipe == 'awal') return date('Y') . '-' . $angka . '-01 00:00:00';
        $tglAkhir = date('t', strtotime(date('Y') . '-' . $angka . '-01'));
        return date('Y') . '-' . $angka . '-' . $tglAkhir . ' 23:59:59';
    }
}