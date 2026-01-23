<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nasabah;
use App\Models\Petugas;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
<<<<<<< HEAD
use Illuminate\Support\Facades\Validator;
=======
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740


class AuthController extends Controller
{
    public function loginPetugas(Request $request)
    {
        //validasi input
<<<<<<< HEAD
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
=======
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
        //find by username
        $petugas = Petugas::where('username', $request->username)->first();

        // validasi petugas ada atau tidak dan cek password
        if (! $petugas || ! Hash::check($request->password, $petugas->password)) {
<<<<<<< HEAD
            return response()->json([
                'message' => 'Username dan Password salah.'
            ], 401);
        }

        //klo berhasil dapat kartu akses/token dgn nama 'token-petugas'
        $token = $petugas->createToken('token-petugas')->plainTextToken;
=======
            // Jika ya, kirim error "Kredensial salah"
            throw ValidationException::withMessages([
                'username' => ['Kredensial yang diberikan salah.'],
            ]);
        }

        //klo berhasil dapat kartu akses/token dgn nama 'auth_token_petugas'
        $token = $petugas->createToken('auth_token_petugas')->plainTextToken;
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740

        //kirim respon json kl berhasil
        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
<<<<<<< HEAD
            'role' => $petugas->role,
=======
            'token_type' => 'Bearer',
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
            'user' => $petugas
        ]);
    }

    public function loginNasabah(Request $request)
    {
<<<<<<< HEAD
        // --- PERUBAHAN DI SINI ---
        // Validasi input: sekarang pakai 'no_rekening' bukan 'username'
        $validator = Validator::make($request->all(), [
            'no_rekening' => 'required|string', 
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Cari nasabah berdasarkan no_rekening
        $nasabah = Nasabah::where('no_rekening', $request->no_rekening)->first();

        // Cek nasabah ada atau tidak dan cek password
        if (! $nasabah || ! Hash::check($request->password, $nasabah->password)) {
            return response()->json([
                'message' => 'Nomor Rekening atau Password salah.'
            ], 401);
        }

        // Cek status nasabah
        // Pastikan kolom 'status' ada di database, jika belum migrasi, baris ini bisa dikomentari sementara
        if ($nasabah->status != 'aktif') {
            throw ValidationException::withMessages([
                'no_rekening' => ['Akun ini sudah tidak aktif (status: ' . $nasabah->status . ').'],
            ]);
        }

        // Generate token
        $token = $nasabah->createToken('token-nasabah')->plainTextToken;

        // Kirim respon json
        // Pastikan nama key sesuai dengan yang diminta Android ('token' dan 'data')
        return response()->json([
            'message' => 'Login Berhasil',
            'token' => $token, 
            'data' => $nasabah
=======
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
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
        ]);
    }

    public function logout(Request $request)
    {
<<<<<<< HEAD
        // Menghapus token akses saat ini
        $request->user()->currentAccessToken()->delete();

        // Respon json
        return response()->json(['message' => 'Logout Berhasil']);
    }
}
=======
        //menghapus token akses saat ini
        $request->user()->currentAccessToken()->delete();

        // respon json
        return response()->json(['message' => 'Logout berhasil']);
    }
}
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
