<x-admin-layout title="Productos">
    <header class="page-head">
        <p class="page-head__eyebrow">Catálogos</p>
        <h1 class="page-head__title">Productos</h1>
        <p class="page-head__lead">Catálogo activo de mercancía y precios de distribución.</p>
    </header>

    <section class="panel">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Compra</th>
                        <th>Distribución</th>
                        <th>Mínimo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $p)
                        <tr>
                            <td>{{ $p->codigo }}</td>
                            <td>{{ $p->nombre }}</td>
                            <td>{{ $p->categoria }}</td>
                            <td>${{ number_format($p->precio_compra, 0, ',', '.') }}</td>
                            <td>${{ number_format($p->precio_distribucion, 0, ',', '.') }}</td>
                            <td>{{ $p->stock_minimo }}</td>
                            <td>
                                <span class="badge {{ $p->activo ? 'badge--ok' : 'badge--warn' }}">
                                    {{ $p->activo ? 'activo' : 'inactivo' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-admin-layout>
