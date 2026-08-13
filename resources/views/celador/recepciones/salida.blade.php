<x-celador-layout title="Registrar salida">
    <header class="page-head">
        <p class="page-head__eyebrow">DA-REC-030 · RF-23</p>
        <h1 class="page-head__title">Registrar salida</h1>
        <p class="page-head__lead">
            {{ $recepcion->codigo_recepcion }} · {{ $recepcion->placa }} · {{ $recepcion->proveedor }}
        </p>
    </header>

    @if ($recepcion->estado === 'pendiente')
        <div class="flash flash--err" style="margin-bottom:1rem">
            Esta recepción aún está en pendiente (sin descarga confirmada). Puedes registrar la salida, pero lo ideal es esperar al bodeguero.
        </div>
    @endif

    <section class="panel">
        <form method="POST" action="{{ route('celador.recepciones.salida.store', $recepcion->id) }}" class="form-grid">
            @csrf
            <div class="field field--full">
                <label for="salida_observaciones">Observaciones de salida</label>
                <textarea id="salida_observaciones" name="salida_observaciones" placeholder="Ej. Sale tras descargar">{{ old('salida_observaciones') }}</textarea>
            </div>
            <div class="field field--full" style="flex-direction:row;gap:.7rem">
                <button class="btn btn-gold" type="submit">Confirmar salida</button>
                <a class="btn" href="{{ route('celador.recepciones.show', $recepcion->id) }}">Cancelar</a>
            </div>
        </form>
    </section>
</x-celador-layout>
