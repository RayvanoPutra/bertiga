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
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $credentials = $request->only('username', 'password');

        // 2. Proses Login dengan Guard 'api_petugas'
        if (! $token = Auth::guard('petugas')->attempt($credentials)) {
            return response()->json(['error' => 'Login Petugas Gagal. Cek username/password.'], 401);
        }
        return $this->respondWithToken($token, 'petugas');
    }

    public function loginNasabah(Request $request)
    {
        $request->validate([
            'no_rekening' => 'required',
            'password' => 'required'
        ]);

        $credentials = $request->only('no_rekening', 'password');

        // Auth::guard('api_nasabah')->attempt($credentials) melakukan 3 hal:
        // a. Hashing password input & mencocokkan dengan database (Bcrypt).
        // b. Jika cocok, library membuat Header & Payload JSON.
        // c. Melakukan SIGNING (Tanda Tangan) menggunakan algoritma HS256 dengan JWT_SECRET.

        if (! $token = Auth::guard('nasabah')->attempt($credentials)) {
            return response()->json(['error' => 'Login Gagal. Cek email & password.'], 401);
        }

        return $this->respondWithToken($token);
    }

    // Fungsi Format Respon JSON
    protected function respondWithToken($token, $guard)
    {
        // Ambil User yang sedang login berdasarkan guard
        $user = Auth::guard($guard)->user();

        $role = ($guard == 'nasabah') ? 'nasabah' : $user->role;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $user,
            'user_info' => [
                'id' => $user->getKey(), // ID User
                'nama' => $guard == 'nasabah' ? $user->nama_siswa : $user->nama_petugas,
                'nama_petugas' => $guard == 'petugas' ? $user->nama_petugas : null,
                'nama_siswa' => $guard == 'nasabah' ? $user->nama_siswa : null,
            ],
            'expires_in' => Auth::guard('nasabah')->factory()->getTTL() * 60 // Default 60 menit
        ]);
    }

    // Cek User yang sedang login (Membaca Token)
    public function meNasabah()
    {
        // 1. Ambil data user dari token
        $user = Auth::guard('nasabah')->user();

        // 2. Tambahkan 'role' manual (Karena di tabel nasabah tidak ada kolom role)
        // Kita ubah objek user jadi Array dulu, baru tambah role
        $userData = $user->toArray();
        $userData['role'] = 'nasabah';

        return response()->json($userData);
    }

    public function mePetugas()
    {
        // 1. Ambil data petugas
        $user = Auth::guard('petugas')->user();

        // Tidak perlu manipulasi array karena di tabel petugas SUDAH ADA kolom role
        return response()->json($user);
    }
    // Logout (Blacklist Token)
    public function logout()
    {
        if (Auth::guard('petugas')->check()) {
            Auth::guard('petugas')->logout();
        } elseif (Auth::guard('nasabah')->check()) {
            Auth::guard('nasabah')->logout();
        }

        return response()->json(['message' => 'Successfully logged out']);
    }
}
