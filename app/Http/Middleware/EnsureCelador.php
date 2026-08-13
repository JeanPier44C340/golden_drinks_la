<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCelador
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->rol || $user->rol->nombre !== 'celador') {
            abort(403, 'Acceso solo para celadores.');
        }

        if ($user->estado !== 'activo') {
            abort(403, 'Tu cuenta no está activa.');
        }

        return $next($request);
    }
}
