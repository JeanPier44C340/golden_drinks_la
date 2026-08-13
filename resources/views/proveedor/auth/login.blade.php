<x-guest-layout>
    <p class="auth-panel__eyebrow">Portal proveedor</p>
    <h1 class="auth-panel__title">Acceso proveedor</h1>
    <p class="auth-panel__lead">Consulta entregas, órdenes, daños y facturación en GoldenSys.</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('proveedor.login.store') }}">
        @csrf

        <div class="field">
            <x-input-label for="email" value="Correo" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="field">
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" class="block mt-1 w-full"
                type="password"
                name="password"
                required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="actions">
            <x-primary-button>
                Entrar al portal
            </x-primary-button>
        </div>
    </form>

    <p class="auth-panel__footer">
        Personal interno?
        <a href="{{ route('login') }}">Login operativo</a>
    </p>
</x-guest-layout>
