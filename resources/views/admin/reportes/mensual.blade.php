<x-admin-layout title="Informe mensual">
    <header class="page-head">
        <p class="page-head__eyebrow">Informe mensual</p>
        <h1 class="page-head__title">{{ sprintf('%02d', $mes) }}/{{ $anio }}</h1>
        <p class="page-head__lead">Resumen operativo del periodo seleccionado.</p>
    </header>

    <form class="toolbar" method="GET" action="{{ route('admin.reportes.mensual') }}">
        <div class="field">
            <label for="mes">Mes</label>
            <input id="mes" type="number" min="1" max="12" name="mes" value="{{ $mes }}">
        </div>
        <div class="field">
            <label for="anio">Año</label>
            <input id="anio" type="number" min="2020" max="2100" name="anio" value="{{ $anio }}">
        </div>
        <button class="btn btn-gold" type="submit">Consultar</button>
        <a class="btn" href="{{ route('admin.reportes.index') }}">Volver</a>
    </form>

    <section class="kpi-strip">
        <article class="kpi"><p class="kpi__label">Recepciones</p><p class="kpi__value">{{ $kpis['recepciones'] }}</p></article>
        <article class="kpi"><p class="kpi__label">Despachos</p><p class="kpi__value">{{ $kpis['despachos'] }}</p></article>
        <article class="kpi"><p class="kpi__label">Pérdidas</p><p class="kpi__value">{{ $kpis['perdidas'] }}</p></article>
        <article class="kpi"><p class="kpi__label">Pedidos aprobados</p><p class="kpi__value">{{ $kpis['pedidos_aprobados'] }}</p></article>
    </section>

    <section class="panel">
        <div class="panel__head"><h2 class="panel__title">Top productos despachados</h2></div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Código</th><th>Producto</th><th>Unidades</th></tr>
                </thead>
                <tbody>
                    @forelse ($top as $row)
                        <tr>
                            <td>{{ $row->codigo }}</td>
                            <td>{{ $row->nombre }}</td>
                            <td>{{ $row->unidades }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty">Sin despachos en este periodo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-admin-layout>
