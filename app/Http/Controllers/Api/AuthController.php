<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nasabah;
use App\Models\Petugas;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    public function loginPetugas(Request $request)
    {
        //validasi input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        //find by username
        $petugas = Petugas::where('username', $request->username)->first();

        // validasi petugas ada atau tidak dan cek password
        if (! $petugas || ! Hash::check($request->password, $petugas->password)) {
            // Jika ya, kirim error "Kredensial salah"
            throw ValidationException::withMessages([
                'username' => ['Kredensial yang diberikan salah.'],
            ]);
        }

        //klo berhasil dapat kartu akses/token dgn nama 'auth_token_petugas'
        $token = $petugas->createToken('auth_token_petugas')->plainTextToken;

        //kirim respon json kl berhasil
        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $petugas
        ]);
    }

    public function loginNasabah(Request $request)
    {
        //validasi input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        //find by username
        $nasabah = Nasabah::where('username', $request->username)->first();

        //cek nasabah ada atau tidak dan cek password
        if (! $nasabah || ! Hash::check($request->password, $nasabah->password)) {
            throw ValidationException::withMessages([
                'username' => ['Kredensial yang diberikan salah.'],
            ]);
        }

        //cek status nasabah
        if ($nasabah->status != 'aktif') {
            throw ValidationException::withMessages([
                'username' => ['Akun ini sudah tidak aktif (status: ' . $nasabah->status . ').'],
            ]);
        }

        //klo berhasil dapat akses/token'
        $token = $nasabah->createToken('auth_token_nasabah')->plainTextToken;

        //kirim respon json kl berhasil
        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $nasabah
        ]);
    }

    public function logout(Request $request)
    {
        //menghapus token akses saat ini
        $request->user()->currentAccessToken()->delete();

        // respon json
        return response()->json(['message' => 'Logout berhasil']);
    }
}
