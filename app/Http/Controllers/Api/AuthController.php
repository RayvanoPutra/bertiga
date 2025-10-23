<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Petugas;
use App\Models\Siswa;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Handle login untuk Petugas
    public function loginPetugas(Request $request)
    {
        // Validasi input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find petugas by username
        $petugas = Petugas::where('username', $request->username)->first();

        // Cek petugas dan password
        if (! $petugas || ! Hash::check($request->password, $petugas->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Buat token baru
        $token = $petugas->createToken('auth_token')->plainTextToken;

        // Respon  
        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $petugas
        ]);
    }

    public function loginSiswa(Request $request)
    {
        //Validasi input
        $request->validate([
            'nis' => 'required',
            'password' => 'required',
        ]);

        // Cari siswa berdasarkan NIS
        $siswa = Siswa::where('nis', $request->nis)->first();

        //Cek siswa & password
        if (! $siswa || ! Hash::check($request->password, $siswa->password)) {
            // kirim error
            throw ValidationException::withMessages([
                'nis' => ['Kredensial yang diberikan salah.'],
            ]);
        }

        // Jika berhasil, buat token
        $token = $siswa->createToken('auth_token_siswa')->plainTextToken;

        // response
        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $siswa
        ]);
    }

    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }
}
