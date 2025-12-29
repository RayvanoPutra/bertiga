<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Pastikan user sudah login dan memiliki role super_admin
        if ($request->user() && $request->user()->role === 'superadmin') {
            return $next($request);
        }

        return response()->json([
            'message' => 'Akses Ditolak! Hanya Super Admin yang diizinkan.'
        ], 403);
    }
}