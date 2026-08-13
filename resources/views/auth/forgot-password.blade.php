<x-guest-layout>
    <p class="auth-panel__eyebrow">Recuperación</p>
    <h1 class="auth-panel__title">Restablecer acceso</h1>
    <p class="auth-panel__lead">
        Indica tu correo y te enviaremos un enlace para elegir una nueva contraseña.
    </p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="field">
            <x-input-label for="email" value="Correo" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="actions" style="justify-content: flex-end;">
            <x-primary-button>
                Enviar enlace
            </x-primary-button>
        </div>
    </form>

    <p class="auth-panel__footer">
        <a href="{{ route('login') }}">Volver al inicio de sesión</a>
    </p>
</x-guest-layout>
