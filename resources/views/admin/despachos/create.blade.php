<x-admin-layout title="Nuevo despacho">
    <header class="page-head">
        <p class="page-head__eyebrow">Crear despacho</p>
        <h1 class="page-head__title">Asignar salida</h1>
        <p class="page-head__lead">Selecciona repartidor y líneas de producto con stock disponible.</p>
    </header>

    <section class="panel">
        <form method="POST" action="{{ route('admin.despachos.store') }}" class="form-grid">
            @csrf
            <div class="field">
                <label for="codigo_despacho">Código</label>
                <input id="codigo_despacho" name="codigo_despacho" value="{{ old('codigo_despacho', 'DESP-'.now()->format('ymdHis')) }}" required>
            </div>
            <div class="field">
                <label for="repartidor_id">Repartidor</label>
                <select id="repartidor_id" name="repartidor_id" required>
                    @foreach ($repartidores as $rep)
                        <option value="{{ $rep->id }}">{{ $rep->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field field--full">
                <label for="pedido_id">Pedido (opcional)</label>
                <select id="pedido_id" name="pedido_id">
                    <option value="">Sin pedido</option>
                    @foreach ($pedidos as $pedido)
                        <option value="{{ $pedido->id }}">{{ $pedido->codigo_pedido }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field field--full">
                <label>Productos</label>
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Stock</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productos as $i => $producto)
                                <tr>
                                    <td>
                                        {{ $producto->codigo }} · {{ $producto->nombre }}
                                        <input type="hidden" name="productos[{{ $i }}][producto_id]" value="{{ $producto->producto_id }}">
                                    </td>
                                    <td>{{ $producto->stock_actual }}</td>
                                    <td>
                                        <input type="number" min="0" max="{{ $producto->stock_actual }}" name="productos[{{ $i }}][cantidad]" value="0" style="width:6rem">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="field field--full" style="flex-direction:row;gap:.7rem">
                <button class="btn btn-gold" type="submit">Registrar despacho</button>
                <a class="btn" href="{{ route('admin.despachos.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
</x-admin-layout>
