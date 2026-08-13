<x-admin-layout title="Usuarios">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-ADM-011 · Seguridad</p>
        <h1 class="page-head__title">Usuarios y roles</h1>
        <p class="page-head__lead">Crea cuentas internas y controla su estado operativo.</p>
    </header>

    <section class="panel" style="margin-bottom:1rem">
        <div class="panel__head">
            <h2 class="panel__title">Nuevo usuario</h2>
        </div>
        <form method="POST" action="{{ route('admin.usuarios.store') }}" class="form-grid">
            @csrf
            <div class="field">
                <label for="nombre_completo">Nombre completo</label>
                <input id="nombre_completo" name="nombre_completo" value="{{ old('nombre_completo') }}" required>
            </div>
            <div class="field">
                <label for="correo">Correo</label>
                <input id="correo" type="email" name="correo" value="{{ old('correo') }}" required>
            </div>
            <div class="field">
                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" required>
            </div>
            <div class="field">
                <label for="telefono">Teléfono</label>
                <input id="telefono" name="telefono" value="{{ old('telefono') }}">
            </div>
            <div class="field">
                <label for="rol_id">Rol</label>
                <select id="rol_id" name="rol_id" required>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->id }}" @selected(old('rol_id') == $rol->id)>{{ $rol->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="justify-content:end">
                <label>&nbsp;</label>
                <button class="btn btn-gold" type="submit">Crear usuario</button>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="panel__head">
            <h2 class="panel__title">Directorio</h2>
            <span class="panel__meta">{{ $usuarios->count() }} cuentas</span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->nombre_completo }}</td>
                            <td>{{ $usuario->correo }}</td>
                            <td><span class="badge badge--gold">{{ $usuario->rol?->nombre }}</span></td>
                            <td>
                                <span class="badge {{ $usuario->estado === 'activo' ? 'badge--ok' : 'badge--warn' }}">
                                    {{ $usuario->estado }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.usuarios.estado', $usuario) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="estado" onchange="this.form.submit()">
                                        @foreach (['activo','inactivo','bloqueado'] as $estado)
                                            <option value="{{ $estado }}" @selected($usuario->estado === $estado)>{{ $estado }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-admin-layout>
