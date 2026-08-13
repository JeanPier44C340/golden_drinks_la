<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProveedor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('proveedor')->user();

        if (! $user) {
            abort(403, 'Acceso solo para proveedores.');
        }

        if ($user->estado !== 'activo') {
            Auth::guard('proveedor')->logout();
            abort(403, 'Tu cuenta de proveedor no está activa.');
        }

        return $next($request);
    }
}
