<x-bodeguero-layout title="Pendientes">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-BOD-003 · RF-03</p>
        <h1 class="page-head__title">Recepciones pendientes</h1>
        <p class="page-head__lead">Tareas visibles para bodega: confirma la descarga y cierra la recepción como descargada.</p>
    </header>

    <section class="panel">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Placa</th>
                        <th>Proveedor</th>
                        <th>Orden</th>
                        <th>Llegada</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recepciones as $r)
                        <tr>
                            <td><strong>{{ $r->codigo_recepcion }}</strong></td>
                            <td>{{ $r->placa }} · {{ $r->conductor }}</td>
                            <td>{{ $r->proveedor }}</td>
                            <td>{{ $r->codigo_orden ?? '—' }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($r->hora_llegada)->format('d/m/Y H:i') }}</td>
                            <td>
                                <a class="btn btn-sm btn-gold" href="{{ route('bodeguero.recepciones.descarga', $r->id) }}">Confirmar descarga</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">No hay pendientes. El celador registra las llegadas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-bodeguero-layout>
