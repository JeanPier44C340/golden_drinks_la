<x-celador-layout title="En bodega">
    <header class="page-head">
        <p class="page-head__eyebrow">Ciclo en bodega</p>
        <h1 class="page-head__title">Vehículos en bodega</h1>
        <p class="page-head__lead">Recepciones sin salida. Tras la descarga del bodeguero, registra la salida.</p>
        <div class="page-head__actions">
            <a class="btn btn-gold" href="{{ route('celador.llegadas.create') }}">Nueva llegada</a>
        </div>
    </header>

    <section class="panel">
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
                    @forelse ($recepciones as $r)
                        <tr>
                            <td><strong>{{ $r->codigo_recepcion }}</strong></td>
                            <td>{{ $r->placa }} · {{ $r->conductor }}</td>
                            <td>{{ $r->proveedor }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($r->hora_llegada)->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $r->estado === 'descargada' ? 'badge--ok' : 'badge--warn' }}">
                                    {{ $r->estado }}
                                </span>
                            </td>
                            <td style="display:flex;gap:.4rem;flex-wrap:wrap">
                                <a class="btn btn-sm" href="{{ route('celador.recepciones.show', $r->recepcion_id) }}">Detalle</a>
                                <a class="btn btn-sm btn-gold" href="{{ route('celador.recepciones.salida', $r->recepcion_id) }}">Salida</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">No hay vehículos en bodega.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-celador-layout>
