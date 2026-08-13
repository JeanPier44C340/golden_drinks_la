<x-repartidor-layout title="Confirmar entrega">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-DES-032 · RF-25</p>
        <h1 class="page-head__title">Confirmar entrega</h1>
        <p class="page-head__lead">
            {{ $despacho->codigo_despacho }}
            · La foto es obligatoria para cerrar el despacho.
        </p>
    </header>

    <section class="panel" style="margin-bottom:1rem">
        <div class="panel__head">
            <h2 class="panel__title">Carga a entregar</h2>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Cant.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($detalle as $line)
                        <tr>
                            <td>{{ $line->codigo }}</td>
                            <td>{{ $line->nombre }}</td>
                            <td>{{ $line->cantidad }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <form method="POST" action="{{ route('repartidor.despachos.entregar.store', $despacho->id) }}" enctype="multipart/form-data" class="form-grid">
            @csrf
            <div class="field field--full">
                <label for="evidencia">Foto de evidencia</label>
                <input id="evidencia" type="file" name="evidencia" accept="image/*" capture="environment" required>
            </div>
            <div class="field">
                <label for="latitud">Latitud (opcional)</label>
                <input id="latitud" type="number" step="any" name="latitud" value="{{ old('latitud') }}">
            </div>
            <div class="field">
                <label for="longitud">Longitud (opcional)</label>
                <input id="longitud" type="number" step="any" name="longitud" value="{{ old('longitud') }}">
            </div>
            <div class="field field--full" style="flex-direction:row;gap:.7rem">
                <button class="btn btn-gold" type="submit">Confirmar entrega</button>
                <a class="btn" href="{{ route('repartidor.despachos.show', $despacho->id) }}">Cancelar</a>
            </div>
        </form>
    </section>
</x-repartidor-layout>
