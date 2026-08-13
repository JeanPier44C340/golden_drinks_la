<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('proveedor')->check()) {
            return redirect()->route('proveedor.entregas.index');
        }

        return view('proveedor.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($credentials['email']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        $ok = Auth::guard('proveedor')->attempt([
            'correo' => $credentials['email'],
            'password' => $credentials['password'],
            'estado' => 'activo',
        ]);

        $proveedorId = DB::table('proveedores')->where('correo', $credentials['email'])->value('id');

        $this->audit(
            $request,
            $ok ? (int) Auth::guard('proveedor')->id() : (int) ($proveedorId ?? 0),
            'login',
            $ok ? 'exitoso' : 'fallido'
        );

        if (! $ok) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('proveedor.entregas.index', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $id = (int) (Auth::guard('proveedor')->id() ?? 0);
        $this->audit($request, $id, 'logout', 'exitoso');

        Auth::guard('proveedor')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('proveedor.login');
    }

    private function audit(Request $request, int $usuarioId, string $accion, string $resultado): void
    {
        if ($usuarioId <= 0) {
            return;
        }

        DB::table('sesiones_auditoria')->insert([
            'usuario_tipo' => 'proveedor',
            'usuario_id' => $usuarioId,
            'accion' => $accion,
            'ip_origen' => $request->ip() ?? '0.0.0.0',
            'navegador' => Str::limit((string) $request->userAgent(), 180, ''),
            'resultado' => $resultado,
        ]);
    }
}
