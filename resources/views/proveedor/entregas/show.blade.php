<x-proveedor-layout title="Detalle entrega">
    <header class="page-head">
        <p class="page-head__eyebrow">{{ $entrega->codigo_recepcion }}</p>
        <h1 class="page-head__title">Detalle de entrega</h1>
        <p class="page-head__lead">
            {{ $entrega->placa }} · {{ $entrega->conductor }}
            @if ($entrega->codigo_orden)
                · Orden {{ $entrega->codigo_orden }}
            @endif
        </p>
        <div class="page-head__actions">
            <a class="btn" href="{{ route('proveedor.entregas.index') }}">Volver</a>
        </div>
    </header>

    <section class="panel">
        <div class="panel__head">
            <h2 class="panel__title">Productos descargados</h2>
            <span class="panel__meta">
                @if ($detalle->isNotEmpty())
                    Bodeguero: {{ $detalle->first()->bodeguero ?? '—' }}
                @endif
            </span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Recibida</th>
                        <th>Dañada</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($detalle as $line)
                        <tr>
                            <td>{{ $line->codigo }} · {{ $line->nombre }}</td>
                            <td>{{ $line->cantidad_recibida }}</td>
                            <td>{{ $line->cantidad_danada }}</td>
                            <td>{{ $line->motivo_dano ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">Aún sin descarga confirmada por bodega.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-proveedor-layout>
