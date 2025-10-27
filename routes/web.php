<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\Auth\LoginController; 
use Illuminate\Support\Facades\Route;


Route::get('/login', function() {
    return view('login');
})->name('login'); 



Route::post('/login', [LoginController::class, 'authenticate'])->name('login.post'); 
Route::middleware(['auth'])->group(function (){

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    //  Super Admin
    Route::middleware(['role:super_admin'])->group(function () {
        Route::get('/super-admin/dashboard', [SuperAdminController::class, 'index'])->name('super_admin.dashboard');
        
        
        Route::get('/super-admin/manage-admin', [SuperAdminController::class, 'manageAdmin'])->name('super_admin.manage_admin');
        
      
        Route::post('/super-admin/store-admin', [SuperAdminController::class, 'storeAdmin'])->name('super_admin.store_admin');
    });

    // Admin
    Route::middleware(['role:admin'])->group(function () { 
        Route::get('/admin/dashboard', [DashboardController::class, 'adminIndex'])->name('admin.dashboard');
    });
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout'); 
});

// route admin_nasabah
route::get('/super_admin/admin_nasabah', function() {
    return view('super_admin.admin_nasabah');
})->name('super_admin.admin_nasabah');



Route::get('/', function () {
    return view('welcome');
});