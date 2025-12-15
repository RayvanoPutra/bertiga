<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Petugas;
use App\Models\Nasabah;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function loginPetugas(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
    
        $petugas = Petugas::where('username', $request->username)->first();
    
        if (!$petugas || !Hash::check($request->password, $petugas->password)) {
            throw ValidationException::withMessages([
                'username' => ['Kredensial yang diberikan salah.'],
            ]);
        }
    
        // ✅ Ganti: Kembalikan token dalam respons JSON
        $token = $petugas->createToken('auth_token_petugas')->plainTextToken;
    
        return response()->json([
            'message' => 'Login Petugas berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $petugas
        ]);
        
        // ❌ Hapus: session(['access_token' => $token]);
        // ❌ Hapus: return redirect()->route('dashboard');
    }
    

    


    public function loginNasabah(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $nasabah = Nasabah::where('username', $request->username)->first();

        if (!$nasabah || !Hash::check($request->password, $nasabah->password)) {
            throw ValidationException::withMessages([
                'username' => ['Kredensial yang diberikan salah.'],
            ]);
        }

        if ($nasabah->status != 'aktif') {
            throw ValidationException::withMessages([
                'username' => ['Akun ini sudah tidak aktif (status: ' . $nasabah->status . ').'],
            ]);
        }

        // Generate token
        $token = $nasabah->createToken('auth_token_nasabah')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $nasabah
        ]);
    }

    public function logout(Request $request)
{
    // Menghapus token akses saat ini (yang digunakan untuk request ini)
    $request->user()->currentAccessToken()->delete();

    return response()->json(['message' => 'Logout berhasil']);
}
}
