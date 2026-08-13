<x-guest-layout>
    <p class="auth-panel__eyebrow">Confirmación</p>
    <h1 class="auth-panel__title">Confirma tu contraseña</h1>
    <p class="auth-panel__lead">
        Zona segura de GoldenSys. Confirma tu contraseña para continuar.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="field">
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" class="block mt-1 w-full"
                type="password"
                name="password"
                required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="actions" style="justify-content: flex-end;">
            <x-primary-button>
                Confirmar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
