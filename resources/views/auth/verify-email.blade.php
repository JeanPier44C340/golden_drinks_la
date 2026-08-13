<x-guest-layout>
    <p class="auth-panel__eyebrow">Verificación</p>
    <h1 class="auth-panel__title">Confirma tu correo</h1>
    <p class="auth-panel__lead">
        Gracias por registrarte. Revisa tu bandeja y haz clic en el enlace de verificación.
        Si no llegó, podemos enviarlo de nuevo.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            Se envió un nuevo enlace de verificación a tu correo.
        </div>
    @endif

    <div class="actions">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>
                Reenviar correo
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="link-muted" style="background:none;border:none;cursor:pointer;padding:0;font:inherit;">
                Cerrar sesión
            </button>
        </form>
    </div>
</x-guest-layout>
