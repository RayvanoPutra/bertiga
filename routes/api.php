<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\NasabahController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\LaporanController;
// use App\Http\Controllers\Api\MasterController;
use App\Http\Controllers\Api\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==========================
// RUTE PUBLIK (Tanpa Token)
// ==========================
Route::post('/login/petugas', [AuthController::class, 'loginPetugas']);
Route::post('/login/nasabah', [AuthController::class, 'loginNasabah']);

Route::post('/proxy-login', function (Request $request) {
    $proxyRequest = Request::create('/api/login/petugas', 'POST', $request->all());
    $proxyRequest->headers->set('Accept', 'application/json');
    return Route::dispatch($proxyRequest);
});

Route::post('/forgot-password/request', [ForgotPasswordController::class, 'requestOtp']);
Route::post('/forgot-password/verify', [ForgotPasswordController::class, 'verifyOtp']);
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword']);


// ===========================================
// RUTE DILINDUNGI (Admin & Super Admin)
// ===========================================
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/pengaturan', [MasterDataController::class, 'getPengaturan']);

    //statistik dashboard
    Route::get('/dashboard/stats', [NasabahController::class, 'getDashboardStats']);

    
    Route::get('transaksi/cetak-laporan', [App\Http\Controllers\Api\LaporanController::class, 'cetakLaporanAndroid']);

    // Data Profil
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        if ($user instanceof \App\Models\Nasabah) {
            return \App\Models\Nasabah::where('no_rekening', $user->no_rekening)
                ->with(['kelas.jurusan'])
                ->first();
        }
        return $user;
    });

    // 1. AKSES BACA MASTER DATA (Dikeluarakan dari Super Admin agar Admin tidak 403)
    Route::prefix('master')->group(function () {
        Route::get('/jurusan', [MasterDataController::class, 'getJurusan']);
        Route::get('/tahun-ajaran', [MasterDataController::class, 'getTahunAjaran']);
        Route::get('/kelas', [MasterDataController::class, 'getKelas']);

        // Data Nasabah (Bisa dikelola Admin & Super Admin)
        Route::get('/nasabah', [NasabahController::class, 'index']);
        Route::post('/nasabah', [NasabahController::class, 'store']);
        Route::put('/nasabah/{no_rekening}', [NasabahController::class, 'updateNasabah']);
        Route::post('/nasabah/bulk-update-status', [NasabahController::class, 'bulkUpdateStatus']);
        Route::delete('/nasabah/{no_rekening}', [NasabahController::class, 'deleteNasabah']);
    });

    // 2. TRANSAKSI (Admin & Super Admin)
    Route::prefix('transaksi')->group(function () {
        Route::post('/request-setor', [TransaksiController::class, 'requestSetor']);
        Route::post('/request-tarik', [TransaksiController::class, 'requestTarik']);
        Route::get('/history', [TransaksiController::class, 'getHistoryNasabah']);
        Route::get('/pending', [TransaksiController::class, 'getPending']);
        Route::post('/approve/{id}', [TransaksiController::class, 'approve']);
        Route::post('/reject/{id}', [TransaksiController::class, 'reject']);
        Route::get('/history-admin', [TransaksiController::class, 'getHistoryAdmin']);
    });

    Route::put('/nasabah/update', [NasabahController::class, 'updateProfile']);


    // ==========================================
    // RUTE KHUSUS SUPER ADMIN (MODIFIKASI DATA)
    // ==========================================
    Route::middleware('super_admin')->group(function () {

        Route::post('/pengaturan/update', [MasterDataController::class, 'updatePengaturan']);

        Route::prefix('master')->group(function () {

            // Manajemen Akun Petugas (Hanya Super Admin)
            Route::get('/petugas', [MasterDataController::class, 'getPetugas']);
            Route::post('/petugas', [MasterDataController::class, 'storePetugas']);
            Route::delete('/petugas/{kode_petugas}', [MasterDataController::class, 'deletePetugas']);

            // Create, Update, Delete Jurusan
            Route::post('/jurusan', [MasterDataController::class, 'storeJurusan']);
            Route::put('/jurusan/{kode_jurusan}', [MasterDataController::class, 'updateJurusan']);
            Route::delete('/jurusan/{kode_jurusan}', [MasterDataController::class, 'deleteJurusan']);

            // Create, Update, Delete Tahun Ajaran
            Route::post('/tahun-ajaran', [MasterDataController::class, 'storeTahunAjaran']);
            Route::put('/tahun-ajaran/{id}', [MasterDataController::class, 'updateTahunAjaran']);
            Route::delete('/tahun-ajaran/{id}', [MasterDataController::class, 'deleteTahunAjaran']);

            // Create, Update, Delete Kelas
            Route::post('/kelas', [MasterDataController::class, 'storeKelas']);
            Route::put('/kelas/{kode_kelas}', [MasterDataController::class, 'updateKelas']);
            Route::delete('/kelas/{kode_kelas}', [MasterDataController::class, 'deleteKelas']);

            // Pengaturan Biaya
            Route::get('/pengaturan/biaya-admin', [MasterDataController::class, 'getBiayaAdmin']);
            Route::get('/master/proses-potongan-tahunan', [TransaksiController::class, 'cronJobPotongan']);



        });

        // Pengaturan Sistem

    });

    Route::get('/cetak-pdf', [TransaksiController::class, 'cetakPdf']);
});