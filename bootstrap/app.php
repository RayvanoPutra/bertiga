<?php

<<<<<<< HEAD
    use Illuminate\Foundation\Application;
    use Illuminate\Foundation\Configuration\Exceptions;
    use Illuminate\Foundation\Configuration\Middleware;

    return Application::configure(basePath: dirname(__DIR__))
        ->withRouting(
            web: __DIR__.'/../routes/web.php',
            api: __DIR__.'/../routes/api.php',
            commands: __DIR__.'/../routes/console.php',
            health: '/up',
        )
        ->withMiddleware(function (Middleware $middleware) {
            // --- TAMBAHKAN BARIS INI UNTUK MEMATIKAN CORS BAWAAN ---
            $middleware->alias([
            'super_admin' => \App\Http\Middleware\CheckSuperAdmin::class,
        ]);
            // -------------------------------------------------------
        })
        ->withExceptions(function (Exceptions $exceptions) {
            //
        })->create();
=======
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
