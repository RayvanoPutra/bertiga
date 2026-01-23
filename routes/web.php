<?php

use Illuminate\Support\Facades\Route;

<<<<<<< HEAD
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

=======
Route::get('/', function () {
    return view('welcome');
});
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
