<x-admin-layout title="Pedido {{ $pedido->codigo_pedido }}">
    <header class="page-head">
        <p class="page-head__eyebrow">Pedido · {{ $pedido->codigo_pedido }}</p>
        <h1 class="page-head__title">{{ $pedido->empresa }}</h1>
        <p class="page-head__lead">
            {{ $pedido->nombre_contacto }} · {{ $pedido->vendedor_correo }} · pago {{ $pedido->pago_estado }} · estado {{ str_replace('_', ' ', $pedido->estado) }}
        </p>
        <div class="page-head__actions">
            @if ($pedido->pago_estado !== 'verificado')
                <form method="POST" action="{{ route('admin.pedidos.verificar-pago', $pedido->id) }}">
                    @csrf
                    <button class="btn" type="submit">Verificar pago</button>
                </form>
            @endif
            @if ($pedido->estado === 'en_revision')
                <form method="POST" action="{{ route('admin.pedidos.aprobar', $pedido->id) }}">
                    @csrf
                    <button class="btn btn-gold" type="submit">Aprobar</button>
                </form>
                <form method="POST" action="{{ route('admin.pedidos.rechazar', $pedido->id) }}">
                    @csrf
                    <button class="btn btn-danger" type="submit">Rechazar</button>
                </form>
            @endif
            <a class="btn" href="{{ route('admin.pedidos.index') }}">Volver</a>
        </div>
    </header>

    <div class="grid-2">
        <section class="panel">
            <div class="panel__head"><h2 class="panel__title">Detalle</h2></div>
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cant.</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($detalle as $line)
                            <tr>
                                <td>{{ $line->codigo }} · {{ $line->nombre }}</td>
                                <td>{{ $line->cantidad_solicitada }}</td>
                                <td>${{ number_format($line->precio_unitario, 0, ',', '.') }}</td>
                                <td>${{ number_format($line->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <div class="panel__head"><h2 class="panel__title">Comprobantes</h2><span class="panel__meta">HU-VEN-031</span></div>
            <ul class="list-soft">
                @forelse ($pagos as $pago)
                    <li>
                        <strong>{{ $pago->tipo_archivo }} · ${{ number_format($pago->monto ?? 0, 0, ',', '.') }}</strong>
                        <span>{{ $pago->referencia }} · {{ $pago->archivo_url }}</span>
                    </li>
                @empty
                    <li><strong>Sin archivos</strong><span>Aún no hay comprobante cargado.</span></li>
                @endforelse
            </ul>
        </section>
    </div>
</x-admin-layout>
