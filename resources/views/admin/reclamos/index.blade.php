<x-admin-layout title="Reclamos">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-VEN-022</p>
        <h1 class="page-head__title">Reclamos de vendedores</h1>
        <p class="page-head__lead">Seguimiento y respuesta a incidencias post-despacho.</p>
    </header>

    <section class="panel">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pedido</th>
                        <th>Vendedor</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reclamos as $r)
                        <tr>
                            <td>#{{ $r->id }}</td>
                            <td>{{ $r->codigo_pedido }}</td>
                            <td>{{ $r->empresa }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($r->descripcion, 60) }}</td>
                            <td><span class="badge">{{ str_replace('_', ' ', $r->estado) }}</span></td>
                            <td><a class="btn btn-sm" href="{{ route('admin.reclamos.show', $r->id) }}">Abrir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">No hay reclamos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-admin-layout>
