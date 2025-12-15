<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Mail\OtpMail;
use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf; // Library PDF

class LaporanController extends Controller
{
    // 1. Minta OTP
    public function requestOtp(Request $request)
    {
        $user = $request->user();

        // Generate 6 angka acak
        $otp = rand(100000, 999999);

        // Simpan di Cache selama 5 menit (300 detik)
        // Key-nya unik per user: "otp_1001"
        Cache::put('otp_' . $user->id, $otp, 300);

        // Kirim Email
        try {
            Mail::to($user->email)->send(new OtpMail($otp, $user->nama));
            return response()->json(['message' => 'Kode OTP telah dikirim ke ' . $user->email]);
        } catch (\Exception $e) {
            // Kita kirim pesan error ASLI dari sistem ke Android untuk debugging
            return response()->json([
                'message' => 'Gagal kirim email. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // 2. Verifikasi & Download PDF
    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|numeric']);
        
        $user = $request->user();
        $cachedOtp = Cache::get('otp_' . $user->id);

        // Cek OTP
        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['message' => 'Kode OTP salah atau kadaluarsa.'], 401);
        }

        // --- OTP BENAR! GENERATE PDF ---
        
        // Hapus OTP agar tidak bisa dipakai lagi
        Cache::forget('otp_' . $user->id);

        // Ambil Data Riwayat
        $transaksi = Transaksi::with('jenisTransaksi')
            ->where('no_rekening', $user->no_rekening)
            ->where('status', '!=', 'pending')
            ->orderBy('tgl_transaksi', 'desc')
            ->get();

        // Load View PDF (Kita buat view ini nanti)
        $pdf = Pdf::loadView('pdf.laporan_keuangan', [
            'nasabah' => $user,
            'transaksi' => $transaksi
        ]);

        // Simpan PDF ke Folder Public Storage
        $fileName = 'Laporan_' . $user->no_rekening . '_' . time() . '.pdf';
        $path = 'public/laporan/' . $fileName;
        Storage::put($path, $pdf->output());

        // Buat URL agar Android bisa download
        // URL: http://ip-server/storage/laporan/namafile.pdf
        $url = asset('storage/laporan/' . $fileName);

        return response()->json([
            'message' => 'Verifikasi Berhasil',
            'url' => $url
        ]);
    }
}