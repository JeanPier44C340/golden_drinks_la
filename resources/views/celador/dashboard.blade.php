<x-celador-layout title="Dashboard">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-REC · Portería</p>
        <h1 class="page-head__title">Dashboard del celador</h1>
        <p class="page-head__lead">Control de llegada y salida de vehículos en la bodega GoldenDrinks.</p>
        <div class="page-head__actions">
            <a class="btn btn-gold" href="{{ route('celador.llegadas.create') }}">Registrar llegada</a>
            <a class="btn" href="{{ route('celador.bodega.index') }}">Ver en bodega</a>
        </div>
    </header>

    <section class="kpi-strip" aria-label="Indicadores del día">
        <article class="kpi">
            <p class="kpi__label">Llegadas hoy</p>
            <p class="kpi__value">{{ $llegadasHoy }}</p>
        </article>
        <article class="kpi">
            <p class="kpi__label">En bodega</p>
            <p class="kpi__value">{{ $enBodega }}</p>
            <p class="kpi__hint">{{ $pendientesSalida }} listas para salida</p>
        </article>
        <article class="kpi">
            <p class="kpi__label">Salidas hoy</p>
            <p class="kpi__value">{{ $salidasHoy }}</p>
        </article>
        <article class="kpi">
            <p class="kpi__label">Atención</p>
            <p class="kpi__value">{{ $pendientesSalida }}</p>
            <p class="kpi__hint">Descargadas sin salida</p>
        </article>
    </section>

    <section class="panel">
        <div class="panel__head">
            <h2 class="panel__title">Vehículos en bodega</h2>
            <span class="panel__meta">Ahora</span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Placa</th>
                        <th>Proveedor</th>
                        <th>Llegada</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($enBodegaLista as $r)
                        <tr>
                            <td><strong>{{ $r->codigo_recepcion }}</strong></td>
                            <td>{{ $r->placa }} · {{ $r->conductor }}</td>
                            <td>{{ $r->proveedor }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($r->hora_llegada)->format('d/m/Y H:i') }}</td>
                            <td><span class="badge badge--warn">{{ $r->estado }}</span></td>
                            <td>
                                <a class="btn btn-sm" href="{{ route('celador.recepciones.show', $r->recepcion_id) }}">Abrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">No hay vehículos en bodega ahora.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-celador-layout>
