<x-repartidor-layout title="Inventario">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-INV-004 · RF-06</p>
        <h1 class="page-head__title">Inventario móvil</h1>
        <p class="page-head__lead">Consulta de stock en tiempo real con estados Disponible, Stock Bajo y Agotado.</p>
    </header>

    <form class="toolbar" method="GET" action="{{ route('repartidor.inventario.index') }}">
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

    <section class="panel">
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
                        <th>Stock</th>
                        <th>Mínimo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>{{ $item->codigo }}</td>
                            <td>
                                <strong>{{ $item->nombre }}</strong>
                                <div style="color:var(--muted);font-size:.8rem;margin-top:.15rem">{{ $item->categoria }}</div>
                            </td>
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
                        <tr><td colspan="5" class="empty">Sin resultados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-repartidor-layout>
