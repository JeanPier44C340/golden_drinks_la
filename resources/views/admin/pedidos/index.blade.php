<x-admin-layout title="Pedidos">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-ADM-012 · Comercial</p>
        <h1 class="page-head__title">Pedidos de vendedores</h1>
        <p class="page-head__lead">Revisa comprobantes, aprueba o rechaza pedidos en cola.</p>
    </header>

    <section class="panel">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Vendedor</th>
                        <th>Estado</th>
                        <th>Pago</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pedidos as $pedido)
                        <tr>
                            <td><strong>{{ $pedido->codigo_pedido }}</strong></td>
                            <td>{{ $pedido->empresa }}<br><span style="color:var(--muted);font-size:.84rem">{{ $pedido->nombre_contacto }}</span></td>
                            <td><span class="badge">{{ str_replace('_', ' ', $pedido->estado) }}</span></td>
                            <td>
                                <span class="badge {{ $pedido->pago_estado === 'verificado' ? 'badge--ok' : 'badge--warn' }}">
                                    {{ $pedido->pago_estado }}
                                </span>
                            </td>
                            <td>{{ \Illuminate\Support\Carbon::parse($pedido->fecha_pedido)->format('d/m/Y H:i') }}</td>
                            <td><a class="btn btn-sm" href="{{ route('admin.pedidos.show', $pedido->id) }}">Abrir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">No hay pedidos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-admin-layout>
