<x-proveedor-layout title="Mis Entregas">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-PRV-015 · RF-13</p>
        <h1 class="page-head__title">Mis Entregas</h1>
        <p class="page-head__lead">Estado de tus recepciones en bodega GoldenDrinks.</p>
        <div class="page-head__actions">
            <a class="btn btn-gold" href="{{ route('proveedor.ordenes.create') }}">Nueva orden</a>
            @if ($noLeidas > 0)
                <a class="btn" href="{{ route('proveedor.notificaciones.index') }}">Tienes {{ $noLeidas }} notificación(es) sin revisar</a>
            @endif
        </div>
    </header>

    <form class="toolbar" method="GET" action="{{ route('proveedor.entregas.index') }}">
        <div class="field">
            <label for="desde">Desde</label>
            <input id="desde" type="date" name="desde" value="{{ $desde }}">
        </div>
        <div class="field">
            <label for="hasta">Hasta</label>
            <input id="hasta" type="date" name="hasta" value="{{ $hasta }}">
        </div>
        <button class="btn btn-gold" type="submit">Filtrar</button>
    </form>

    <section class="panel">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Código</th>
                        <th>Placa</th>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entregas as $e)
                        @php
                            $label = $e->estado === 'pendiente' ? 'Pendiente de descarga' : 'Descargado';
                            if ($e->estado === 'cancelada') $label = 'Cancelada';
                            $badge = match($e->estado) {
                                'pendiente' => 'badge--warn',
                                'cancelada' => 'badge--danger',
                                default => 'badge--ok',
                            };
                        @endphp
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($e->hora_llegada)->format('d/m/Y H:i') }}</td>
                            <td><strong>{{ $e->codigo_recepcion }}</strong></td>
                            <td>{{ $e->placa }} · {{ $e->conductor }}</td>
                            <td>{{ $e->codigo_orden ?? '—' }}</td>
                            <td><span class="badge {{ $badge }}">{{ $label }}</span></td>
                            <td>
                                <a class="btn btn-sm" href="{{ route('proveedor.entregas.show', $e->id) }}">Detalle</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">Sin entregas en el período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-proveedor-layout>
