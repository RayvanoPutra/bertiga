<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nasabah;
use App\Models\Petugas;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function loginPetugas(Request $request)
    {
        //validasi input
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        //find by username
        $petugas = Petugas::where('username', $request->username)->first();

        // validasi petugas ada atau tidak dan cek password
        if (! $petugas || ! Hash::check($request->password, $petugas->password)) {
            return response()->json([
                'message' => 'Username dan Password salah.'
            ], 401);
        }

        //klo berhasil dapat kartu akses/token dgn nama 'token-petugas'
        $token = $petugas->createToken('token-petugas')->plainTextToken;

        //kirim respon json kl berhasil
        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'role' => $petugas->role,
            'user' => $petugas
        ]);
    }

    public function loginNasabah(Request $request)
    {
        //validasi input
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //find by username
        $nasabah = Nasabah::where('username', $request->username)->first();

        //cek nasabah ada atau tidak dan cek password
        if (! $nasabah || ! Hash::check($request->password, $nasabah->password)) {
            return response()->json([
                'message' => 'Username atau Password salah.'
            ], 401);
        }

        //cek status nasabah
        if ($nasabah->status != 'aktif') {
            throw ValidationException::withMessages([
                'username' => ['Akun ini sudah tidak aktif (status: ' . $nasabah->status . ').'],
            ]);
        }

        //klo berhasil dapat akses/token'
        $token = $nasabah->createToken('token-nasabah')->plainTextToken;

        //kirim respon json kl berhasil
        return response()->json([
            'message' => 'Login Berhasil',
            'token' => $token,
            'data' => $nasabah
        ]);
    }

    public function logout(Request $request)
    {
        //menghapus token akses saat ini
        $request->user()->currentAccessToken()->delete();

        // respon json
        return response()->json(['message' => 'Logout Berhasil']);
    }
}
