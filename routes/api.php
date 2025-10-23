<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| Rute API
|--------------------------------------------------------------------------
*/

// == RUTE AUTENTIKASI (PUBLIK) ==
// Rute ini tidak perlu token
Route::post('/login/petugas', [AuthController::class, 'loginPetugas']);
Route::post('/login/siswa', [AuthController::class, 'loginSiswa']);

// == RUTE YANG TERLINDUNGI (BUTUH TOKEN) ==
// Semua rute di dalam grup ini wajib mengirimkan token
Route::middleware('auth:sanctum')->group(function () {

    // Rute Logout (berlaku untuk petugas & siswa)
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rute tes untuk mengecek token
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rute untuk API kita nanti akan ditaruh di sini
    // ...
});
