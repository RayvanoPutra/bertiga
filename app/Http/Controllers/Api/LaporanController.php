<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Mail\LaporanOtpMail;
use App\Models\Transaksi;
use TCPDF;

class LaporanController extends Controller
{
    // 1. Minta OTP
    // public function requestOtp(Request $request)
    // {
    //     $user = $request->user();

    //     // Generate 6 angka acak
    //     $otp = rand(100000, 999999);

    //     // Simpan di Cache selama 5 menit (300 detik)
    //     // Key-nya unik per user: "otp_1001"
    //     Cache::put('otp_' . $user->id, $otp, 300);

    //     // Kirim Email
    //     try {
    //         Mail::to($user->email)->send(new OtpMail($otp, $user->nama));
    //         return response()->json(['message' => 'Kode OTP telah dikirim ke ' . $user->email]);
    //     } catch (\Exception $e) {
    //         // Kita kirim pesan error ASLI dari sistem ke Android untuk debugging
    //         return response()->json([
    //             'message' => 'Gagal kirim email. Error: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    // 2. Verifikasi & Download PDF
    // public function verifyOtp(Request $request)
    // {
    //     $request->validate(['otp' => 'required|numeric']);

    //     $user = $request->user();
    //     $cachedOtp = Cache::get('otp_' . $user->id);

    //     // Cek OTP
    //     if (!$cachedOtp || $cachedOtp != $request->otp) {
    //         return response()->json(['message' => 'Kode OTP salah atau kadaluarsa.'], 401);
    //     }

    //     // --- OTP BENAR! GENERATE PDF ---

    //     // Hapus OTP agar tidak bisa dipakai lagi
    //     Cache::forget('otp_' . $user->id);

    //     // Ambil Data Riwayat
    //     $transaksi = Transaksi::with('jenisTransaksi')
    //         ->where('no_rekening', $user->no_rekening)
    //         ->where('status', '!=', 'pending')
    //         ->orderBy('tgl_transaksi', 'desc')
    //         ->get();

    //     // Load View PDF (Kita buat view ini nanti)
    //     $pdf = Pdf::loadView('pdf.laporan_keuangan', [
    //         'nasabah' => $user,
    //         'transaksi' => $transaksi
    //     ]);

    //     // Simpan PDF ke Folder Public Storage
    //     $fileName = 'Laporan_' . $user->no_rekening . '_' . time() . '.pdf';
    //     $path = 'public/laporan/' . $fileName;
    //     Storage::put($path, $pdf->output());

    //     // Buat URL agar Android bisa download
    //     // URL: http://ip-server/storage/laporan/namafile.pdf
    //     $url = asset('storage/laporan/' . $fileName);

    //     return response()->json([
    //         'message' => 'Verifikasi Berhasil',
    //         'url' => $url
    //     ]);
    // }

    public function downloadEncryptedPdf(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'bulan' => 'required|numeric',
            'tahun' => 'required|numeric',
        ]);

        // --- PERBAIKAN ERROR 1 (Ganti auth()->user()) ---
        // Kita ambil user dari $request, ini lebih dikenali oleh editor
        $user = $request->user();

        if (!$user->email) {
            return response()->json(['message' => 'Email tidak terdaftar.'], 400);
        }

        // 2. Ambil Data Transaksi
        $transaksi = Transaksi::with('jenisTransaksi')
            ->where('no_rekening', $user->no_rekening)
            ->whereMonth('created_at', $request->bulan)
            ->whereYear('created_at', $request->tahun)
            ->get();

        if ($transaksi->isEmpty()) {
            return response()->json(['message' => 'Tidak ada transaksi pada periode ini.'], 404);
        }

        // 3. Generate OTP
        $otp = rand(100000, 999999);

        // 4. Kirim OTP ke Email
        try {
            // Pastikan class ini sesuai dengan yang kamu Import di atas
            Mail::to($user->email)->send(new LaporanOtpMail($otp, $user->nama));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengirim email: ' . $e->getMessage()], 500);
        }

        // 5. Siapkan Data untuk View PDF
        $data = [
            'nasabah' => $user,
            'transaksi' => $transaksi,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun
        ];

        // Kita render view-nya jadi string HTML dulu
        $html = view('pdf.laporan', $data)->render();
        // 2. Setup TCPDF (PENGGANTI MPDF)
        // Parameter: 'P' (Portrait), 'mm', 'A4', true, 'UTF-8', false
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Hapus Header/Footer bawaan TCPDF (biar bersih seperti DomPDF)
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // --- INI JANTUNGNYA AES (Point of Interest buat Sidang) ---
        // Parameter SetProtection:
        // 1. Permissions: array('print', 'copy')
        // 2. User Password: $otp (Password buat buka)
        // 3. Owner Password: null (Master password, kita samakan atau kosongkan)
        // 4. Mode: 3 (ANGKA SAKTI!) 
        //    -> 0 = RC4 40 bit
        //    -> 1 = RC4 128 bit
        //    -> 2 = AES 128 bit
        //    -> 3 = AES 256 bit (Kita pakai yang paling kuat)

        $pdf->SetProtection(['print', 'copy'], $otp, env('APP_KEY'), 2);

        // 3. Masukkan Konten HTML
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');

        // 4. Output PDF
        $fileName = 'Laporan_' . $request->bulan . '-' . $request->tahun . '.pdf';

        // Trik agar bisa didownload via Laravel Response Stream
        return response()->streamDownload(function () use ($pdf) {
            // 'S' artinya Return as String (biar bisa di-stream)
            echo $pdf->Output('laporan.pdf', 'S');
        }, $fileName);
    }
}
