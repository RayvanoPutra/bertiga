<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
| Rute API
| 
*/

//route publik (tdk perlu token)
Route::post('/login/petugas', [AuthController::class, 'loginPetugas']);
Route::post('/login/nasabah', [AuthController::class, 'loginNasabah']);


//rute terproteksi (perlu token)
Route::middleware('auth:sanctum')->group(function () {

    //route logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rute tes untuk mengecek token (mengembalikan data user yang sedang login)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Nanti, semua API FASE 4 (CRUD, Transaksi, Laporan)
    // akan kita letakkan di dalam grup ini.
    // ...
});
