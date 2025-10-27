<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Jika peran pengguna TIDAK sesuai, tolak akses
        if (auth()->user()->role !== $role) {
            return redirect('/dashboard')->with('error', 'Akses ditolak.');
        }

        return $next($request);
    }

}