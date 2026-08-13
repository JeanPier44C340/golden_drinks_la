<x-bodeguero-layout title="Inventario">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-BOD-026 · RF-06</p>
        <h1 class="page-head__title">Inventario operativo</h1>
        <p class="page-head__lead">Stock en tiempo real con estados visuales Disponible, Stock Bajo y Agotado.</p>
    </header>

    <form class="toolbar" method="GET" action="{{ route('bodeguero.inventario.index') }}">
        <div class="field">
            <label for="q">Buscar</label>
            <input id="q" name="q" value="{{ $q }}" placeholder="Nombre, código o categoría">
        </div>
        <div class="field">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="">Todos</option>
                @foreach (['Disponible','Stock Bajo','Agotado'] as $opt)
                    <option value="{{ $opt }}" @selected($estado === $opt)>{{ $opt }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-gold" type="submit">Filtrar</button>
    </form>

    <section class="panel" style="margin-bottom:1rem">
        <div class="panel__head">
            <h2 class="panel__title">Existencias</h2>
            <span class="panel__meta">{{ $items->count() }} ítems</span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Stock</th>
                        <th>Mínimo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>{{ $item->codigo }}</td>
                            <td>{{ $item->nombre }}</td>
                            <td>{{ $item->categoria }}</td>
                            <td>{{ $item->stock_actual }}</td>
                            <td>{{ $item->stock_minimo }}</td>
                            <td>
                                @php
                                    $badge = match($item->estado_visual) {
                                        'Disponible' => 'badge--ok',
                                        'Stock Bajo' => 'badge--warn',
                                        default => 'badge--danger',
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">{{ $item->estado_visual }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">Sin resultados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel__head">
            <h2 class="panel__title">Movimientos recientes</h2>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Cant.</th>
                        <th>Saldo</th>
                        <th>Nota</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movimientos as $mov)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($mov->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ $mov->codigo }} · {{ $mov->nombre }}</td>
                            <td>{{ $mov->tipo_movimiento }}</td>
                            <td>{{ $mov->cantidad }}</td>
                            <td>{{ $mov->saldo_anterior }} → {{ $mov->saldo_resultante }}</td>
                            <td>{{ $mov->nota }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">Aún no hay movimientos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-bodeguero-layout>
