<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\NasabahController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
// Route tanpa token

// Auth Petugas
Route::post('auth/petugas/login', [AuthController::class, 'loginPetugas']);

// Auth Nasabah
Route::post('auth/nasabah/login', [AuthController::class, 'loginNasabah']);

// Password Reset
Route::prefix('forgot-password')->group(function () {
    Route::post('/request', [ForgotPasswordController::class, 'requestOtp']);
    Route::post('/verify', [ForgotPasswordController::class, 'verifyOtp']);
    Route::post('/reset', [ForgotPasswordController::class, 'resetPassword']);
});


// ===========================================
// Rute Nasabah
// ===========================================
Route::middleware('auth:nasabah')->group(function () {

    // Auth & Profil
    Route::post('auth/nasabah/logout', [AuthController::class, 'logout']);
    Route::get('auth/nasabah/me', [AuthController::class, 'meNasabah']);
    Route::put('/nasabah/update', [NasabahController::class, 'updateProfile']);

    // Transaksi Self-Service
    Route::prefix('transaksi')->group(function () {
        Route::post('/request-setor', [TransaksiController::class, 'requestSetor']);
        Route::post('/request-tarik', [TransaksiController::class, 'requestTarik']);
        Route::get('/history', [TransaksiController::class, 'getHistoryNasabah']);

        // Cetak Laporan (Fitur Android Teman)
        Route::get('/cetak-laporan', [LaporanController::class, 'cetakLaporanAndroid']);
    });
});
// Rute petugas guard
Route::middleware('auth:petugas')->group(function () {

    // Auth & Profil
    Route::post('auth/petugas/logout', [AuthController::class, 'logout']);
    Route::get('auth/petugas/me', [AuthController::class, 'mePetugas']);

    // Dashboard & Statistik
    Route::get('/dashboard/stats', [NasabahController::class, 'getDashboardStats']);
    Route::get('/pengaturan', [MasterDataController::class, 'getPengaturan']);

    // Cetak PDF (Fitur Teman yang mau diambil)
    Route::get('/cetak-pdf', [TransaksiController::class, 'cetakPdf']);

    // --- MANAJEMEN TRANSAKSI ---
    Route::prefix('transaksi')->group(function () {
        Route::get('/pending', [TransaksiController::class, 'getPending']);
        Route::post('/approve/{id}', [TransaksiController::class, 'approve']);
        Route::post('/reject/{id}', [TransaksiController::class, 'reject']);
        Route::get('/history-admin', [TransaksiController::class, 'getHistoryAdmin']);
    });
    // --- MANAJEMEN MASTER DATA (CRUD) ---
    Route::prefix('master')->group(function () {
        // Read Only (Dropdown)
        Route::get('/jurusan', [MasterDataController::class, 'getJurusan']);
        Route::get('/tahun-ajaran', [MasterDataController::class, 'getTahunAjaran']);
        Route::get('/kelas', [MasterDataController::class, 'getKelas']);

        // Kelola Nasabah
        Route::get('/nasabah', [NasabahController::class, 'index']);
        Route::post('/nasabah', [NasabahController::class, 'store']);
        Route::put('/nasabah/{no_rekening}', [NasabahController::class, 'updateNasabah']);
        Route::delete('/nasabah/{no_rekening}', [NasabahController::class, 'deleteNasabah']);
        Route::post('/nasabah/bulk-update-status', [NasabahController::class, 'bulkUpdateStatus']);
    });
    // Super admin routes
    Route::middleware('super_admin')->group(function () {

        Route::post('/pengaturan/update', [MasterDataController::class, 'updatePengaturan']);

        Route::prefix('master')->group(function () {
            // Kelola Akun Petugas
            Route::get('/petugas', [MasterDataController::class, 'getPetugas']);
            Route::post('/petugas', [MasterDataController::class, 'storePetugas']);
            Route::delete('/petugas/{kode_petugas}', [MasterDataController::class, 'deletePetugas']);

            // Kelola Jurusan (Full Access)
            Route::post('/jurusan', [MasterDataController::class, 'storeJurusan']);
            Route::put('/jurusan/{kode_jurusan}', [MasterDataController::class, 'updateJurusan']);
            Route::delete('/jurusan/{kode_jurusan}', [MasterDataController::class, 'deleteJurusan']);

            // Kelola Tahun Ajaran (Full Access)
            Route::post('/tahun-ajaran', [MasterDataController::class, 'storeTahunAjaran']);
            Route::put('/tahun-ajaran/{id}', [MasterDataController::class, 'updateTahunAjaran']);
            Route::delete('/tahun-ajaran/{id}', [MasterDataController::class, 'deleteTahunAjaran']);

            // Kelola Kelas (Full Access)
            Route::post('/kelas', [MasterDataController::class, 'storeKelas']);
            Route::put('/kelas/{kode_kelas}', [MasterDataController::class, 'updateKelas']);
            Route::delete('/kelas/{kode_kelas}', [MasterDataController::class, 'deleteKelas']);

            // Info Biaya
            Route::get('/pengaturan/biaya-admin', [MasterDataController::class, 'getBiayaAdmin']);
        });
    });
});
