<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TransaksiController;
// Redirect root ke login
Route::get('/', function () {
    return redirect('/admin/login');
});

// --- GRUP RUTE ADMIN (View) ---

// 1. Halaman Login
Route::get('/admin/login', function () {
    // Memanggil file: resources/views/admin/login.blade.php
    return view('admin.admin_login'); 
})->name('login');

// 2. Halaman Dashboard / Transaksi
Route::get('/admin/petugas', function () {
    // Memanggil file: resources/views/admin/dashboard.blade.php
    return view('admin.admin_petugas'); 
});

// 3. Halaman Kelola Nasabah
Route::get('/admin/nasabah', function () {
    // Memanggil file: resources/views/admin/nasabah.blade.php
    return view('admin.admin_nasabah'); 
});
// pdf nasabah
Route::get('/admin/nasabah/cetak-pdf', [App\Http\Controllers\Api\NasabahController::class, 'cetakLaporan']);



Route::get('/admin/dashboard', function () {
    // Memanggil file: resources/views/admin/nasabah.blade.php
    return view('admin.admin_dashboard'); 
});

Route::get('/admin/tahunajaran', function () {
    // Memanggil file: resources/views/admin/nasabah.blade.php
    return view('admin.admin_tahunajaran'); 
});

Route::get('/admin/jurusan', function () {
    // Memanggil file: resources/views/admin/nasabah.blade.php
    return view('admin.admin_jurusan'); 
});

Route::get('/admin/kelas', function () {
    // Memanggil file: resources/views/admin/nasabah.blade.php
    return view('admin.admin_kelas'); 
});

Route::get('/admin/pengaturan', function () {
    // Memanggil file: resources/views/admin/nasabah.blade.php
    return view('admin.admin_pengaturan'); 
});

Route::get('/admin/akun', function () {
    // Memanggil file: resources/views/admin/nasabah.blade.php
    return view('admin.admin_akun'); 
});


Route::get('/admin/transaksi', function() {
    return view('admin.admin_transaksi');
});

// pdf transaksi
Route::get('/admin/transaksi/cetak-pdf', [TransaksiController::class, 'cetakPdf'])->name('transaksi.cetak');
Route::get('/admin/transaksi/cetak-struk/{kode}', [TransaksiController::class, 'cetakStruk'])->name('transaksi.struk');


