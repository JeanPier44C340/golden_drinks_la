<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:150', 'unique:usuarios,correo'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $rolId = Rol::query()
            ->where('nombre', 'repartidor')
            ->value('id')
            ?? Rol::query()->orderBy('id')->value('id');

        if (! $rolId) {
            throw ValidationException::withMessages([
                'email' => 'No hay roles configurados en el sistema.',
            ]);
        }

        $user = User::create([
            'nombre_completo' => $request->name,
            'correo' => $request->email,
            'password_hash' => Hash::make($request->password),
            'rol_id' => $rolId,
            'estado' => 'activo',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
