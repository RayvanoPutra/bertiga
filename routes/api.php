<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\NasabahController;
use App\Http\Controllers\Api\TransaksiController;

/*
| Rute API
*/

//route publik (tdk perlu token)
Route::post('/login/petugas', [AuthController::class, 'loginPetugas']);
Route::post('/login/nasabah', [AuthController::class, 'loginNasabah']);


//rute dilindungi (perlu token)
Route::middleware('auth:sanctum')->group(function () {

    //route logout
    Route::post('/logout', [AuthController::class, 'logout']);

    //rute tes untuk mengecek token (mengembalikan data user yang sedang login)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });


    //rute nasabah
    Route::post('/nasabah', [NasabahController::class, 'storeNasabah']);
    Route::post('/nasabah/bulk-update-status', [NasabahController::class, 'bulkUpdateStatus']);
    Route::delete('/nasabah/{no_rekening}', [NasabahController::class, 'deleteNasabah']);

    Route::prefix('transaksi')->group(function () {

        // Rute untuk Nasabah (Android)
        Route::post('/request-setor', [TransaksiController::class, 'requestSetor']);
        Route::post('/request-tarik', [TransaksiController::class, 'requestTarik']);
        Route::get('/history', [TransaksiController::class, 'getHistoryNasabah']);

        // rute untuk Petugas (Web Admin)
        Route::get('/pending', [TransaksiController::class, 'getPending']);
        Route::post('/approve/{id}', [TransaksiController::class, 'approve']);
        Route::post('/reject/{id}', [TransaksiController::class, 'reject']);
    });

    //rute master utk superadmin
    Route::prefix('master')->group(function () {

        //route jurusan crud
        Route::get('/jurusan', [MasterDataController::class, 'getJurusan']);
        Route::post('/jurusan', [MasterDataController::class, 'storeJurusan']);
        Route::get('/jurusan/{kode_jurusan}', [MasterDataController::class, 'showJurusan']);
        Route::put('/jurusan/{kode_jurusan}', [MasterDataController::class, 'updateJurusan']);
        Route::delete('/jurusan/{kode_jurusan}', [MasterDataController::class, 'deleteJurusan']);

        //rute Tahun Ajaran crud
        Route::get('/tahun-ajaran', [MasterDataController::class, 'getTahunAjaran']);
        Route::post('/tahun-ajaran', [MasterDataController::class, 'storeTahunAjaran']);
        Route::put('/tahun-ajaran/{id}', [MasterDataController::class, 'updateTahunAjaran']);
        Route::delete('/tahun-ajaran/{id}', [MasterDataController::class, 'deleteTahunAjaran']);

        //rute Kelas
        Route::get('/kelas', [MasterDataController::class, 'getKelas']);
        Route::post('/kelas', [MasterDataController::class, 'storeKelas']);
        Route::put('/kelas/{id}', [MasterDataController::class, 'updateKelas']);
        Route::delete('/kelas/{id}', [MasterDataController::class, 'deleteKelas']);
    });
});
