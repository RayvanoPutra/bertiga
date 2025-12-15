<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\NasabahController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\LaporanController;

/*
| Rute API
*/

//route publik (tdk perlu token)
Route::post('/login/petugas', [AuthController::class, 'loginPetugas']);
Route::post('/login/nasabah', [AuthController::class, 'loginNasabah']);


// Tambahkan Rute Proxy ini di LUAR grup middleware (biar publik)
Route::post('/proxy-login', function (Request $request) {
    
    // Panggil Login Petugas secara internal
    $proxyRequest = Request::create('/api/login/petugas', 'POST', $request->all());
    
    // Paksa Header Accept JSON agar balikan pasti JSON
    $proxyRequest->headers->set('Accept', 'application/json');
    
    // Jalankan request
    $response = Route::dispatch($proxyRequest);
    
    return $response;
});


//rute dilindungi (perlu token)
Route::middleware('auth:sanctum')->group(function () {

    //route logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rute tes untuk mengecek token & ambil data terbaru
    Route::get('/user', function (Request $request) {
        // 1. Ambil user dari token
        $userToken = $request->user();

        // 2. Cek apakah dia Nasabah
        if ($userToken->tokenCan('role:nasabah') || $userToken instanceof \App\Models\Nasabah) {
            // 3. PAKSA ambil data terbaru dari tabel 'nasabah' berdasarkan no_rekening
            // Menggunakan with() untuk memuat relasi kelas dan jurusan
            $nasabahSegar = \App\Models\Nasabah::where('no_rekening', $userToken->no_rekening)
                ->with(['kelas.jurusan'])
                ->first();
            return $nasabahSegar;
        }

        // Fallback untuk petugas
        return $userToken;
    });


    //rute nasabah
    Route::post('/nasabah', [NasabahController::class, 'storeNasabah']);
    Route::post('/nasabah/bulk-update-status', [NasabahController::class, 'bulkUpdateStatus']);
    Route::delete('/nasabah/{no_rekening}', [NasabahController::class, 'deleteNasabah']);

    Route::prefix('transaksi')->group(function () {

        // Rute untuk Nasabah (Android)
        Route::post('/request', [TransaksiController::class, 'requestTransaksi']);
        Route::get('/history', [TransaksiController::class, 'getHistoryNasabah']);
        // Route::post('/request-tarik', [TransaksiController::class, 'requestTarik']);
        // Route::get('/history', [TransaksiController::class, 'getHistoryNasabah']);

        // rute untuk Petugas (Web Admin)
        Route::get('/history-admin', [TransaksiController::class, 'getHistoryAdmin']);
        Route::get('/pending', [TransaksiController::class, 'getPending']);
        Route::post('/approve/{id}', [TransaksiController::class, 'approve']);
        Route::post('/reject/{id}', [TransaksiController::class, 'reject']);
    });

    //rute master utk superadmin
    Route::prefix('master')->group(function () {

        // === NASABAH ===
        Route::get('/nasabah', [NasabahController::class, 'index']);
        Route::post('/nasabah', [NasabahController::class, 'store']);

        //route jurusan crud
        Route::get('/jurusan', [MasterDataController::class, 'getJurusan']);
        Route::post('/jurusan', [MasterDataController::class, 'storeJurusan']);
        // Route::get('/jurusan/{kode_jurusan}', [MasterDataController::class, 'showJurusan']);
        // Route::put('/jurusan/{kode_jurusan}', [MasterDataController::class, 'updateJurusan']);
        // Route::delete('/jurusan/{kode_jurusan}', [MasterDataController::class, 'deleteJurusan']);

        //rute Tahun Ajaran crud
        Route::get('/tahun-ajaran', [MasterDataController::class, 'getTahunAjaran']);
        Route::post('/tahun-ajaran', [MasterDataController::class, 'storeTahunAjaran']);
        // Route::put('/tahun-ajaran/{id}', [MasterDataController::class, 'updateTahunAjaran']);
        // Route::delete('/tahun-ajaran/{id}', [MasterDataController::class, 'deleteTahunAjaran']);

        //rute Kelas
        Route::get('/kelas', [MasterDataController::class, 'getKelas']);
        Route::post('/kelas', [MasterDataController::class, 'storeKelas']);
        // Route::put('/kelas/{id}', [MasterDataController::class, 'updateKelas']);
        // Route::delete('/kelas/{id}', [MasterDataController::class, 'deleteKelas']);
    });

    //Laporan Keuangan
    Route::post('/laporan/request-otp', [LaporanController::class, 'requestOtp']);
    Route::post('/laporan/verify', [LaporanController::class, 'verifyOtp']);


});