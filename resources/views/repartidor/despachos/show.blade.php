<x-repartidor-layout title="Detalle despacho">
    <header class="page-head">
        <p class="page-head__eyebrow">Despacho · {{ $despacho->codigo_despacho }}</p>
        <h1 class="page-head__title">Detalle de carga</h1>
        <p class="page-head__lead">
            Pedido {{ $despacho->codigo_pedido ?? 'sin pedido' }}
            · Estado <span class="badge badge--gold">{{ $despacho->estado }}</span>
        </p>
        <div class="page-head__actions">
            @if ($despacho->estado === 'creado')
                <form method="POST" action="{{ route('repartidor.despachos.en-camino', $despacho->id) }}">
                    @csrf
                    <button class="btn" type="submit">Iniciar ruta</button>
                </form>
            @endif
            @if (in_array($despacho->estado, ['creado', 'en_camino'], true))
                <a class="btn btn-gold" href="{{ route('repartidor.despachos.entregar', $despacho->id) }}">Confirmar entrega</a>
            @endif
            <a class="btn" href="{{ route('repartidor.despachos.index') }}">Volver</a>
        </div>
    </header>

    <section class="panel" style="margin-bottom:1rem">
        <div class="panel__head">
            <h2 class="panel__title">Productos</h2>
            <span class="panel__meta">{{ $detalle->count() }} líneas</span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($detalle as $line)
                        <tr>
                            <td>{{ $line->codigo }}</td>
                            <td>{{ $line->nombre }}</td>
                            <td>{{ $line->cantidad }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty">Sin detalle.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($evidencia)
        <section class="panel">
            <div class="panel__head">
                <h2 class="panel__title">Evidencia de entrega</h2>
                <span class="panel__meta">{{ \Illuminate\Support\Carbon::parse($evidencia->entregado_en)->format('d/m/Y H:i') }}</span>
            </div>
            <p style="margin-bottom:.8rem;color:var(--muted);font-weight:300">
                Foto registrada.
                @if ($evidencia->latitud && $evidencia->longitud)
                    Ubicación: {{ $evidencia->latitud }}, {{ $evidencia->longitud }}
                @endif
            </p>
            <a class="btn btn-sm" href="{{ $evidencia->archivo_url }}" target="_blank" rel="noopener">Ver foto</a>
        </section>
    @endif
</x-repartidor-layout>
