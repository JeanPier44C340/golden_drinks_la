<x-repartidor-layout title="Dashboard">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-SEC-024 · Ruta</p>
        <h1 class="page-head__title">Dashboard del repartidor</h1>
        <p class="page-head__lead">Inventario móvil y despachos asignados para confirmar entrega con foto.</p>
        <div class="page-head__actions">
            <a class="btn btn-gold" href="{{ route('repartidor.inventario.index') }}">Ver inventario</a>
            <a class="btn" href="{{ route('repartidor.despachos.index') }}">Mis despachos</a>
        </div>
    </header>

    <section class="kpi-strip" aria-label="Indicadores de ruta">
        <article class="kpi">
            <p class="kpi__label">Activos</p>
            <p class="kpi__value">{{ $asignados }}</p>
            <p class="kpi__hint">Creados o en camino</p>
        </article>
        <article class="kpi">
            <p class="kpi__label">En camino</p>
            <p class="kpi__value">{{ $enCamino }}</p>
        </article>
        <article class="kpi">
            <p class="kpi__label">Entregados hoy</p>
            <p class="kpi__value">{{ $entregadosHoy }}</p>
        </article>
        <article class="kpi">
            <p class="kpi__label">Stock bajo</p>
            <p class="kpi__value">{{ $stockBajo }}</p>
            <p class="kpi__hint">Consulta móvil</p>
        </article>
    </section>

    <section class="panel">
        <div class="panel__head">
            <h2 class="panel__title">Despachos pendientes</h2>
            <span class="panel__meta">Asignados</span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Pedido</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($despachosLista as $d)
                        <tr>
                            <td><strong>{{ $d->codigo_despacho }}</strong></td>
                            <td>{{ $d->codigo_pedido ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $d->estado === 'en_camino' ? 'badge--gold' : 'badge--warn' }}">
                                    {{ $d->estado }}
                                </span>
                            </td>
                            <td>
                                <a class="btn btn-sm btn-gold" href="{{ route('repartidor.despachos.show', $d->id) }}">Abrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">No tienes despachos activos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-repartidor-layout>
