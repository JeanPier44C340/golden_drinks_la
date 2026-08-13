<x-guest-layout>
    <p class="auth-panel__eyebrow">Registro</p>
    <h1 class="auth-panel__title">Crear cuenta</h1>
    <p class="auth-panel__lead">Únete a GoldenSys y comienza a gestionar la operación de bodega.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="field">
            <x-input-label for="name" value="Nombre" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="field">
            <x-input-label for="email" value="Correo" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="field">
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" class="block mt-1 w-full"
                type="password"
                name="password"
                required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="field">
            <x-input-label for="password_confirmation" value="Confirmar contraseña" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                type="password"
                name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="actions" style="justify-content: flex-end;">
            <x-primary-button>
                Registrarse
            </x-primary-button>
        </div>
    </form>

    <p class="auth-panel__footer">
        ¿Ya tienes cuenta?
        <a href="{{ route('login') }}">Inicia sesión</a>
    </p>
</x-guest-layout>
