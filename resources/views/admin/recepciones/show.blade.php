<x-admin-layout title="Recepción {{ $recepcion->codigo_recepcion }}">
    <header class="page-head">
        <p class="page-head__eyebrow">{{ $recepcion->codigo_recepcion }}</p>
        <h1 class="page-head__title">{{ $recepcion->proveedor }}</h1>
        <p class="page-head__lead">
            {{ $recepcion->placa }} · {{ $recepcion->conductor }} · celador {{ $recepcion->celador ?? '—' }}
        </p>
        <div class="page-head__actions">
            <a class="btn" href="{{ route('admin.recepciones.index') }}">Volver</a>
        </div>
    </header>

    <section class="panel">
        <div class="panel__head"><h2 class="panel__title">Detalle de descarga</h2></div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Recibido</th>
                        <th>Dañado</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($detalle as $line)
                        <tr>
                            <td>{{ $line->codigo }} · {{ $line->nombre }}</td>
                            <td>{{ $line->cantidad_recibida }}</td>
                            <td>{{ $line->cantidad_danada }}</td>
                            <td>{{ $line->motivo_dano ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">Sin detalle de descarga.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-admin-layout>
