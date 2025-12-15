<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// --- TAMBAHAN RUTE BARU ---
Route::get('/panel-petugas', function () {
    // Ini memanggil file resources/views/admin_petugas.blade.php
    return view('admin_petugas'); 
});