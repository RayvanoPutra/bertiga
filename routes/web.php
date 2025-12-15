<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// File: routes/web.php

Route::get('/login', function () {
    return view('login'); // Pastikan ini mengarah ke login.blade.php Anda
})->name('login');
// Proses login (POST)
Route::post('/login', [AuthController::class, 'loginPetugas'])->name('login.submit');

// dashboard
Route::get('/superadmin/dashboard', function () {
    return view('super_admin.dashboard');
})->name('superadmin.dashboard'); // Tanpa middleware sementara

// Kelas
Route::get('/superadmin/kelas', function () {
    return view('super_admin.kelas'); 
})->name('superadmin.kelas');

// jurusan
Route::get('/superadmin/jurusan', function () {
    return view('super_admin.jurusan');
})->name('superadmin.jurusan');

// TahunAjaran
// web.php
Route::get('/superadmin/tahun_ajaran', function () { // Perbaiki URL juga
    return view('super_admin.tahun_ajaran');
})->name('superadmin.tahun_ajaran'); // Perbaiki nama rute


// nasabah
Route::get('/superadmin/nasabah', function() {
    return view('super_admin.nasabah');
})->name('superadmin.nasabah');

Route::get('/test-route-200', function () {
    return 'Route Web Aktif!';
});
