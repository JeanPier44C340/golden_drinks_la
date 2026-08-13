<x-repartidor-layout title="Despachos">
    <header class="page-head">
        <p class="page-head__eyebrow">Despachos asignados</p>
        <h1 class="page-head__title">Mis despachos</h1>
        <p class="page-head__lead">Cargas que el administrador te asignó. Confirma cada entrega con foto.</p>
    </header>

    <section class="panel">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Pedido</th>
                        <th>Estado</th>
                        <th>Despachado</th>
                        <th>Entregado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($despachos as $d)
                        <tr>
                            <td><strong>{{ $d->codigo_despacho }}</strong></td>
                            <td>{{ $d->codigo_pedido ?? '—' }}</td>
                            <td>
                                @php
                                    $badge = match($d->estado) {
                                        'entregado' => 'badge--ok',
                                        'en_camino' => 'badge--gold',
                                        'cancelado' => 'badge--danger',
                                        default => 'badge--warn',
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">{{ $d->estado }}</span>
                            </td>
                            <td>{{ $d->despachado_en ? \Illuminate\Support\Carbon::parse($d->despachado_en)->format('d/m/Y H:i') : '—' }}</td>
                            <td>{{ $d->entregado_en ? \Illuminate\Support\Carbon::parse($d->entregado_en)->format('d/m/Y H:i') : '—' }}</td>
                            <td>
                                <a class="btn btn-sm" href="{{ route('repartidor.despachos.show', $d->id) }}">Detalle</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">Aún no tienes despachos asignados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-repartidor-layout>
