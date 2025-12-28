<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nasabah;
use App\Mail\OtpMail; // Pakai mail yang sudah ada
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    // 1. Request OTP (Kirim ke Email)
    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_rekening' => 'required|string',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Cek apakah No Rekening & Email cocok
        $nasabah = Nasabah::where('no_rekening', $request->no_rekening)
            ->where('email', $request->email)
            ->first();

        if (!$nasabah) {
            return response()->json(['message' => 'Data tidak ditemukan. Pastikan No. Rekening dan Email benar.'], 404);
        }

        // Generate OTP
        $otp = rand(100000, 999999);
        
        // Simpan OTP di Cache (Key: forgot_norek, Value: otp, 5 menit)
        Cache::put('forgot_' . $nasabah->no_rekening, $otp, 300);

        // Kirim Email (Pakai Mailable yang sudah ada)
        try {
            Mail::to($nasabah->email)->send(new OtpMail($otp, $nasabah->nama));
            return response()->json(['message' => 'Kode OTP telah dikirim ke email Anda.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal kirim email: ' . $e->getMessage()], 500);
        }
    }

    // 2. Verifikasi OTP (Cek apakah kode benar)
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'no_rekening' => 'required|string',
            'otp' => 'required|numeric'
        ]);

        $cachedOtp = Cache::get('forgot_' . $request->no_rekening);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['message' => 'Kode OTP salah atau kadaluarsa.'], 401);
        }

        // OTP Benar, kirim sinyal 'OK' agar Android pindah ke layar reset password
        // Kita tidak hapus OTP dulu, karena akan dipakai lagi untuk validasi akhir saat ganti password
        return response()->json(['message' => 'OTP Valid. Silakan buat password baru.']);
    }

    // 3. Reset Password (Simpan Password Baru)
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_rekening' => 'required|string',
            'otp' => 'required|numeric',
            'new_password' => 'required|string|min:6|confirmed' // Pastikan ada field 'new_password_confirmation' dari Android (atau hapus 'confirmed' jika tidak kirim)
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Cek OTP lagi (Security Check)
        $cachedOtp = Cache::get('forgot_' . $request->no_rekening);
        
        // Debugging: Jika ingin bypass OTP sementara, komentar bagian if ini
        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['message' => 'Sesi kadaluarsa atau OTP salah. Ulangi proses.'], 401);
        }

        // --- PERBAIKAN UTAMA DI SINI ---
        // Gunakan metode update() langsung pada Query Builder
        // Ini lebih aman untuk Primary Key tipe String (No Rekening)
        
        $updateStatus = Nasabah::where('no_rekening', $request->no_rekening)
            ->update([
                'password' => Hash::make($request->new_password)
            ]);

        if ($updateStatus) {
            // Hapus OTP dari cache
            Cache::forget('forgot_' . $request->no_rekening);
            
            return response()->json(['message' => 'Password BERHASIL diubah. Silakan login dengan password baru.']);
        } else {
            return response()->json(['message' => 'Gagal mengupdate database. Pastikan No Rekening benar.'], 500);
        }
    }
}