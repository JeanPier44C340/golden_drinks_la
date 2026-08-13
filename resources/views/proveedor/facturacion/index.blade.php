<x-proveedor-layout title="Facturación">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-PRV-018 · RF-17 · RN-13</p>
        <h1 class="page-head__title">Resumen de facturación</h1>
        <p class="page-head__lead">Genera el resumen de entregas del período y descárgalo cuando lo necesites.</p>
    </header>

    <section class="panel" style="margin-bottom:1rem">
        <form method="POST" action="{{ route('proveedor.facturacion.store') }}" class="form-grid">
            @csrf
            <div class="field">
                <label for="periodo_desde">Desde</label>
                <input id="periodo_desde" type="date" name="periodo_desde" value="{{ old('periodo_desde', now()->startOfMonth()->toDateString()) }}" required>
            </div>
            <div class="field">
                <label for="periodo_hasta">Hasta</label>
                <input id="periodo_hasta" type="date" name="periodo_hasta" value="{{ old('periodo_hasta', now()->toDateString()) }}" required>
            </div>
            <div class="field field--full" style="flex-direction:row;gap:.7rem">
                <button class="btn btn-gold" type="submit">Generar resumen</button>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="panel__head">
            <h2 class="panel__title">Historial de reportes</h2>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Generado</th>
                        <th>Período</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportes as $r)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($r->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ $r->periodo_desde }} → {{ $r->periodo_hasta }}</td>
                            <td>
                                <a class="btn btn-sm btn-gold" href="{{ route('proveedor.facturacion.download', $r->id) }}">Descargar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty">Aún no has generado reportes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-proveedor-layout>
