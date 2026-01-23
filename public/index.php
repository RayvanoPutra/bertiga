<?php

<<<<<<< HEAD
// =======================================================================
// TAMBAHAN: IZINKAN AKSES DARI MANA SAJA (CORS)
// =======================================================================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With');

// Jika browser bertanya "Boleh gak?" (Preflight/OPTIONS), langsung jawab "BOLEH!" (200 OK)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
// =======================================================================

=======
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

<<<<<<< HEAD
=======
// Determine if the application is in maintenance mode...
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

<<<<<<< HEAD
require __DIR__.'/../vendor/autoload.php';

(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
=======
// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
>>>>>>> e5f3843eb7a0b900deedde262c235ad8fe8f0740
