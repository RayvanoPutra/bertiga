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
| 1. AUTHENTICATION ROUTES (Login & Reset Password)
|--------------------------------------------------------------------------
*/

// Auth Petugas
Route::prefix('auth/petugas')->group(function () {
    Route::post('login', [AuthController::class, 'loginPetugas']);
    
});

// Auth Nasabah
Route::prefix('auth/nasabah')->group(function () {
    Route::post('login', [AuthController::class, 'loginNasabah']);
});

// Reset Password (Public)
Route::post('/forgot-password/request', [ForgotPasswordController::class, 'requestOtp']);
Route::post('/forgot-password/verify', [ForgotPasswordController::class, 'verifyOtp']);
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword']);


/*
|--------------------------------------------------------------------------
| 2. AREA NASABAH (Self Service)
|--------------------------------------------------------------------------
| Middleware: auth:nasabah (Hanya Nasabah Login yang bisa akses)
*/
Route::middleware('auth:nasabah')->group(function () {

    // Manajemen Sesi
    Route::post('/auth/nasabah/logout', [AuthController::class, 'logout']);
    Route::get('/auth/nasabah/me', [AuthController::class, 'meNasabah']);

    // Profil
    Route::put('/nasabah/update', [NasabahController::class, 'updateProfile']);

    // Transaksi Mandiri (Siswa Request Sendiri)
    Route::prefix('transaksi')->group(function () {
        Route::post('/request-setor', [TransaksiController::class, 'requestSetor']); // Siswa input setor
        Route::post('/request-tarik', [TransaksiController::class, 'requestTarik']); // Siswa input tarik
        Route::get('/history', [TransaksiController::class, 'getHistoryNasabah']);   // Lihat history sendiri
    });
});


/*
|--------------------------------------------------------------------------
| 3. AREA PETUGAS (Admin & Super Admin)
|--------------------------------------------------------------------------
| Middleware: auth:petugas (Hanya Petugas Login yang bisa akses)
*/
Route::middleware('auth:petugas')->group(function () {

    // Manajemen Sesi
    Route::post('/auth/petugas/logout', [AuthController::class, 'logout']);
    Route::get('/auth/petugas/me', [AuthController::class, 'mePetugas']);

    // Dashboard & Utils
    Route::get('/dashboard/stats', [NasabahController::class, 'getDashboardStats']);
    Route::get('/pengaturan', [MasterDataController::class, 'getPengaturan']);

    // Fitur Keamanan Laporan
    Route::post('/laporan/request-otp', [LaporanController::class, 'requestOtp']);
    Route::post('/laporan/verify', [LaporanController::class, 'verifyOtp']);
    Route::post('/laporan/download-encrypted', [LaporanController::class, 'downloadEncryptedPdf']);

    // --- APPROVAL TRANSAKSI ---
    Route::prefix('transaksi')->group(function () {
        Route::get('/pending', [TransaksiController::class, 'getPending']); // Cek request masuk
        Route::post('/approve/{id}', [TransaksiController::class, 'approve']); // Setujui
        Route::post('/reject/{id}', [TransaksiController::class, 'reject']);   // Tolak
        Route::get('/history-admin', [TransaksiController::class, 'getHistoryAdmin']); // Semua history
    });

    // --- MASTER DATA (Bisa Diakses Admin & Super Admin) ---
    Route::prefix('master')->group(function () {
        // Read Only Data Sekolah
        Route::get('/jurusan', [MasterDataController::class, 'getJurusan']);
        Route::get('/tahun-ajaran', [MasterDataController::class, 'getTahunAjaran']);
        Route::get('/kelas', [MasterDataController::class, 'getKelas']);

        // Kelola Data Nasabah
        Route::get('/nasabah', [NasabahController::class, 'index']);
        Route::post('/nasabah', [NasabahController::class, 'store']);
        Route::put('/nasabah/{no_rekening}', [NasabahController::class, 'updateNasabah']);
        Route::delete('/nasabah/{no_rekening}', [NasabahController::class, 'deleteNasabah']);
        Route::post('/nasabah/bulk-update-status', [NasabahController::class, 'bulkUpdateStatus']);
    });

    // ==========================================
    // RUTE KHUSUS SUPER ADMIN
    // ==========================================
    Route::middleware('super_admin')->group(function () {

        Route::post('/pengaturan/update', [MasterDataController::class, 'updatePengaturan']);

        Route::prefix('master')->group(function () {
            // Manajemen Akun Petugas
            Route::get('/petugas', [MasterDataController::class, 'getPetugas']);
            Route::post('/petugas', [MasterDataController::class, 'storePetugas']);
            Route::delete('/petugas/{kode_petugas}', [MasterDataController::class, 'deletePetugas']);

            // CRUD Jurusan
            Route::post('/jurusan', [MasterDataController::class, 'storeJurusan']);
            Route::put('/jurusan/{kode_jurusan}', [MasterDataController::class, 'updateJurusan']);
            Route::delete('/jurusan/{kode_jurusan}', [MasterDataController::class, 'deleteJurusan']);

            // CRUD Tahun Ajaran
            Route::post('/tahun-ajaran', [MasterDataController::class, 'storeTahunAjaran']);
            Route::put('/tahun-ajaran/{kode_tahun_ajaran}', [MasterDataController::class, 'updateTahunAjaran']);
            Route::delete('/tahun-ajaran/{kode_tahun_ajaran}', [MasterDataController::class, 'deleteTahunAjaran']);

            // CRUD Kelas
            Route::post('/kelas', [MasterDataController::class, 'storeKelas']);
            Route::put('/kelas/{kode_kelas}', [MasterDataController::class, 'updateKelas']);
            Route::delete('/kelas/{kode_kelas}', [MasterDataController::class, 'deleteKelas']);

            // Pengaturan Biaya
            Route::get('/pengaturan/biaya-admin', [MasterDataController::class, 'getBiayaAdmin']);
        });
    });
});
