<x-bodeguero-layout title="Dashboard">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-BOD · Bodega</p>
        <h1 class="page-head__title">Dashboard del bodeguero</h1>
        <p class="page-head__lead">Confirma descargas, consulta inventario y registra dañados en bodega.</p>
        <div class="page-head__actions">
            <a class="btn btn-gold" href="{{ route('bodeguero.pendientes.index') }}">Ver pendientes</a>
            <a class="btn" href="{{ route('bodeguero.inventario.index') }}">Inventario</a>
        </div>
    </header>

    <section class="kpi-strip" aria-label="Indicadores operativos">
        <article class="kpi">
            <p class="kpi__label">Pendientes</p>
            <p class="kpi__value">{{ $pendientes }}</p>
            <p class="kpi__hint">Sin descarga</p>
        </article>
        <article class="kpi">
            <p class="kpi__label">Descargas hoy</p>
            <p class="kpi__value">{{ $descargasHoy }}</p>
        </article>
        <article class="kpi">
            <p class="kpi__label">Stock bajo</p>
            <p class="kpi__value">{{ $stockBajo }}</p>
            <p class="kpi__hint">Bajo o agotado</p>
        </article>
        <article class="kpi">
            <p class="kpi__label">Pérdidas hoy</p>
            <p class="kpi__value">{{ $perdidasHoy }}</p>
        </article>
    </section>

    <section class="panel">
        <div class="panel__head">
            <h2 class="panel__title">Recepciones pendientes</h2>
            <span class="panel__meta">Descarga</span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Placa</th>
                        <th>Proveedor</th>
                        <th>Llegada</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendientesLista as $r)
                        <tr>
                            <td><strong>{{ $r->codigo_recepcion }}</strong></td>
                            <td>{{ $r->placa }} · {{ $r->conductor }}</td>
                            <td>{{ $r->proveedor }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($r->hora_llegada)->format('d/m/Y H:i') }}</td>
                            <td>
                                <a class="btn btn-sm btn-gold" href="{{ route('bodeguero.recepciones.descarga', $r->id) }}">Descargar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">No hay recepciones pendientes de descarga.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-bodeguero-layout>
