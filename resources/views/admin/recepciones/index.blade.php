<x-admin-layout title="Recepciones">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-REP-008</p>
        <h1 class="page-head__title">Historial de recepciones</h1>
        <p class="page-head__lead">Ciclo de vehículos en bodega: llegada, descarga y salida.</p>
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
                        <th>Situación</th>
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
                            <td><span class="badge">{{ $r->situacion }}</span></td>
                            <td>{{ $r->estado }}</td>
                            <td><a class="btn btn-sm" href="{{ route('admin.recepciones.show', $r->recepcion_id) }}">Detalle</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">No hay recepciones.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-admin-layout>
