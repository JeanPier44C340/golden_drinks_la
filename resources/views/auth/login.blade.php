<x-guest-layout>
    <p class="auth-panel__eyebrow">Acceso</p>
    <h1 class="auth-panel__title">Iniciar sesión</h1>
    <p class="auth-panel__lead">Entra a GoldenSys para operar la bodega de GoldenDrinks.</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
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

        <label for="remember_me" class="check">
            <input id="remember_me" type="checkbox" name="remember">
            <span>Recordarme</span>
        </label>

        <div class="actions">
            @if (Route::has('password.request'))
                <a class="link-muted" href="{{ route('password.request') }}">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif

            <x-primary-button>
                Entrar
            </x-primary-button>
        </div>
    </form>

    <p class="auth-panel__footer">
        ¿Aún no tienes cuenta?
        <a href="{{ route('register') }}">Regístrate</a>
    </p>
</x-guest-layout>
