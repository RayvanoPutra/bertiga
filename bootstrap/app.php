<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful; // Penting: Pastikan ini di-import

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php', // ✅ Pastikan baris ini ada dan benar
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // 1. Konfigurasi Grup Middleware 'web' (biasanya sudah ada)
        $middleware->web(append: [
            // ... middleware 'web' lainnya
        ]);

        // 2. KONFIGURASI GRUP MIDDLEWARE 'API'
        // Menambahkan EnsureFrontendRequestsAreStateful::class ke grup API
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class, 
        ]);

        // 3. Pendaftaran Route Middleware (ALIAS)
        // Diperlukan agar Anda bisa menggunakan 'auth:sanctum' di routes/api.php
        $middleware->alias([
            'auth:sanctum' => EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ...
    })->create();