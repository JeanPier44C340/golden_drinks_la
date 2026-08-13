<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(): View
    {
        $usuarios = User::query()
            ->with('rol')
            ->orderBy('nombre_completo')
            ->get();

        $roles = Rol::query()->where('activo', true)->orderBy('nombre')->get();

        return view('admin.usuarios.index', compact('usuarios', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email', 'max:150', 'unique:usuarios,correo'],
            'password' => ['required', 'string', 'min:6'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'rol_id' => ['required', 'exists:roles,id'],
        ]);

        User::create([
            'nombre_completo' => $data['nombre_completo'],
            'correo' => $data['correo'],
            'password_hash' => Hash::make($data['password']),
            'telefono' => $data['telefono'] ?? null,
            'rol_id' => $data['rol_id'],
            'estado' => 'activo',
        ]);

        return back()->with('status', 'Usuario creado.');
    }

    public function updateEstado(Request $request, User $usuario): RedirectResponse
    {
        $data = $request->validate([
            'estado' => ['required', Rule::in(['activo', 'inactivo', 'bloqueado'])],
        ]);

        if ($usuario->id === $request->user()->id && $data['estado'] !== 'activo') {
            return back()->withErrors(['estado' => 'No puedes desactivar tu propia cuenta.']);
        }

        $usuario->update(['estado' => $data['estado']]);

        return back()->with('status', 'Estado actualizado.');
    }
}
