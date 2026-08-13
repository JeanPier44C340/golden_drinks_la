<x-admin-layout title="Despachos">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-ADM-028 · HU-DES-006</p>
        <h1 class="page-head__title">Órdenes de despacho</h1>
        <p class="page-head__lead">Asigna mercancía a repartidores y controla el ciclo de salida.</p>
        <div class="page-head__actions">
            <a class="btn btn-gold" href="{{ route('admin.despachos.create') }}">Nuevo despacho</a>
        </div>
    </header>

    <section class="panel">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Pedido</th>
                        <th>Repartidor</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($despachos as $despacho)
                        <tr>
                            <td><strong>{{ $despacho->codigo_despacho }}</strong></td>
                            <td>{{ $despacho->codigo_pedido ?? '—' }}</td>
                            <td>{{ $despacho->repartidor }}</td>
                            <td><span class="badge">{{ str_replace('_', ' ', $despacho->estado) }}</span></td>
                            <td>{{ \Illuminate\Support\Carbon::parse($despacho->despachado_en)->format('d/m/Y H:i') }}</td>
                            <td>
                                @if (! in_array($despacho->estado, ['entregado', 'cancelado'], true))
                                    <form method="POST" action="{{ route('admin.despachos.cancelar', $despacho->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-danger" type="submit">Cancelar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">No hay despachos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-admin-layout>
