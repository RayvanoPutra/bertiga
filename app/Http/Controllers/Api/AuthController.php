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
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'no_rekening' => 'required', // Nasabah login pakai No Rekening
            'password'    => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. Ambil Credentials
        $credentials = $request->only('no_rekening', 'password');

        // 3. Cek ke Guard 'nasabah'
        if (! $token = Auth::guard('nasabah')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'No Rekening atau Password salah.',
            ], 401);
        }

        // 4. Jika sukses, kembalikan Token
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
