<x-admin-layout title="Dashboard" eyebrow="HU-ADM-009">
    <header class="page-head">
        <p class="page-head__eyebrow">Operación · Campoalegre</p>
        <h1 class="page-head__title">Dashboard administrativo</h1>
        <p class="page-head__lead">Resumen del mes: inventario, recepciones, despachos, pérdidas y alertas de stock.</p>
    </header>

    <section class="kpi-strip" aria-label="Indicadores del mes">
        <article class="kpi">
            <p class="kpi__label">Stock total</p>
            <p class="kpi__value">{{ number_format($kpi->stock_total ?? 0) }}</p>
            <p class="kpi__hint">{{ $kpi->total_productos_activos ?? 0 }} productos activos</p>
        </article>
        <article class="kpi">
            <p class="kpi__label">Recepciones</p>
            <p class="kpi__value">{{ number_format($kpi->recepciones_mes ?? 0) }}</p>
            <p class="kpi__hint">{{ $kpi->salidas_vehiculos_mes ?? 0 }} salidas de vehículo</p>
        </article>
        <article class="kpi">
            <p class="kpi__label">Despachos</p>
            <p class="kpi__value">{{ number_format($kpi->despachos_mes ?? 0) }}</p>
            <p class="kpi__hint">{{ number_format($kpi->perdidas_mes ?? 0) }} unidades perdidas</p>
        </article>
        <article class="kpi">
            <p class="kpi__label">Atención</p>
            <p class="kpi__value">{{ number_format($kpi->alertas_activas ?? 0) }}</p>
            <p class="kpi__hint">{{ $kpi->pedidos_en_revision ?? 0 }} pedidos · {{ $kpi->reclamos_abiertos ?? 0 }} reclamos</p>
        </article>
    </section>

    <div class="grid-2">
        <section class="panel">
            <div class="panel__head">
                <h2 class="panel__title">Alertas de stock</h2>
                <span class="panel__meta">HU-INV-005</span>
            </div>
            @if ($alertas->isEmpty() && $inventarioCritico->isEmpty())
                <p class="empty">Sin alertas abiertas por ahora.</p>
            @else
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Stock</th>
                                <th>Mínimo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($alertas as $alerta)
                                <tr>
                                    <td>
                                        <strong>{{ $alerta->codigo }}</strong><br>
                                        <span style="color:var(--muted);font-size:.84rem">{{ $alerta->nombre }}</span>
                                    </td>
                                    <td>{{ $alerta->stock_detectado }}</td>
                                    <td>{{ $alerta->stock_minimo }}</td>
                                    <td><span class="badge badge--danger">Abierta</span></td>
                                </tr>
                            @empty
                                @foreach ($inventarioCritico as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->codigo }}</strong><br>
                                            <span style="color:var(--muted);font-size:.84rem">{{ $item->nombre }}</span>
                                        </td>
                                        <td>{{ $item->stock_actual }}</td>
                                        <td>{{ $item->stock_minimo }}</td>
                                        <td>
                                            <span class="badge {{ $item->estado_visual === 'Agotado' ? 'badge--danger' : 'badge--warn' }}">
                                                {{ $item->estado_visual }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="panel">
            <div class="panel__head">
                <h2 class="panel__title">Prioridades</h2>
                <span class="panel__meta">Hoy</span>
            </div>

            @if ($estrella)
                <p style="margin-bottom:1rem;font-weight:300;color:var(--muted)">
                    Producto estrella del mes:
                    <strong style="color:var(--gold-soft)">{{ $estrella->nombre }}</strong>
                    ({{ $estrella->unidades_despachadas }} u.)
                </p>
            @endif

            <ul class="list-soft">
                @forelse ($pedidosPendientes as $pedido)
                    <li>
                        <strong>
                            <a href="{{ route('admin.pedidos.show', $pedido->id) }}">{{ $pedido->codigo_pedido }}</a>
                        </strong>
                        <span>{{ $pedido->empresa }} · pago {{ $pedido->pago_estado }}</span>
                    </li>
                @empty
                    <li>
                        <strong>Sin pedidos en revisión</strong>
                        <span>La cola comercial está al día.</span>
                    </li>
                @endforelse
            </ul>

            <div class="page-head__actions" style="margin-top:1.2rem">
                <a class="btn btn-gold" href="{{ route('admin.pedidos.index') }}">Ver pedidos</a>
                <a class="btn" href="{{ route('admin.inventario.index') }}">Inventario</a>
            </div>
        </section>
    </div>
</x-admin-layout>
