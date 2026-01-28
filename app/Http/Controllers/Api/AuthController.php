<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nasabah;
use App\Models\Petugas;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function loginPetugas(Request $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. Ambil Credentials (Username & Password)
        $credentials = $request->only('username', 'password');

        // 3. Cek ke Guard 'petugas'
        // attempt() otomatis hash check password & generate token
        if (! $token = Auth::guard('petugas')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau Password salah.',
            ], 401);
        }

        // 4. Jika sukses, kembalikan Token & Data User
        return $this->respondWithToken($token, 'petugas');
    }

    public function loginNasabah(Request $request)
    {
        // 1. VALIDASI INPUT
        $validator = Validator::make($request->all(), [
            'no_rekening' => 'required',
            'password'    => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. CARI NASABAH & LOAD RELASI
        $nasabah = Nasabah::with('kelas.tahunAjaran')
            ->where('no_rekening', $request->no_rekening)
            ->first();

        // 3. CEK PASSWORD MANUAL
        // Kita cek manual dulu biar token GAK ke-create kalau user diblokir
        if (!$nasabah || !Hash::check($request->password, $nasabah->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Login Gagal',
                'error'   => 'No Rekening atau Password salah.'
            ], 401);
        }

        // 4. CEK STATUS PERSONAL (Nonaktif)
        if ($nasabah->status == 'nonaktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak',
                'error'   => 'Akun Anda berstatus NONAKTIF. Silakan hubungi petugas.'
            ], 403);
        }

        // 5. CEK STATUS TAHUN AJARAN (Logic Penjagaan)
        if ($nasabah->kelas && $nasabah->kelas->tahunAjaran && $nasabah->kelas->tahunAjaran->status == 'nonaktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak',
                'error'   => 'Tahun Ajaran kelas Anda (' . $nasabah->kelas->tahunAjaran->tahun_ajaran . ') sudah ditutup/nonaktif.'
            ], 403);
        }

        // 6. GENERATE TOKEN (JWT)
        if (! $token = Auth::guard('nasabah')->login($nasabah)) {
            return response()->json(['error' => 'Gagal membuat token'], 500);
        }

        // 7. RETURN RESPONSE
        return $this->respondWithToken($token, 'nasabah');
    }

    public function logout()
    {
        // Invalidate token yang sedang dipakai
        auth()->logout();

        return response()->json(['message' => 'Berhasil logout']);
    }

    // Cek Profil Petugas
    public function mePetugas()
    {
        return response()->json(Auth::guard('petugas')->user());
    }

    public function meNasabah()
    {
        return response()->json(Auth::guard('nasabah')->user());
    }
    protected function respondWithToken($token, $guard)
    {
        // Ambil data user yang sedang login berdasarkan guard
        $user = Auth::guard($guard)->user();

        return response()->json([
            'success' => true,
            'user'    => $user, // Mengirim data user (nama, role, dll) ke frontend
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => Auth::guard($guard)->factory()->getTTL() * 60
        ]);
    }
}
