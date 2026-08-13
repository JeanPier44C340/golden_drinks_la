<x-admin-layout title="Reportes">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-REP-007</p>
        <h1 class="page-head__title">Reportes operativos</h1>
        <p class="page-head__lead">Consulta el informe mensual y el historial de reportes generados.</p>
        <div class="page-head__actions">
            <a class="btn btn-gold" href="{{ route('admin.reportes.mensual') }}">Informe del mes</a>
        </div>
    </header>

    <section class="kpi-strip">
        <article class="kpi">
            <p class="kpi__label">Stock</p>
            <p class="kpi__value">{{ number_format($resumen->stock_total ?? 0) }}</p>
        </article>
        <article class="kpi">
            <p class="kpi__label">Despachos mes</p>
            <p class="kpi__value">{{ number_format($resumen->despachos_mes ?? 0) }}</p>
        </article>
        <article class="kpi">
            <p class="kpi__label">Recepciones</p>
            <p class="kpi__value">{{ number_format($resumen->recepciones_mes ?? 0) }}</p>
        </article>
        <article class="kpi">
            <p class="kpi__label">Pérdidas</p>
            <p class="kpi__value">{{ number_format($resumen->perdidas_mes ?? 0) }}</p>
        </article>
    </section>

    <section class="panel">
        <div class="panel__head">
            <h2 class="panel__title">Archivos generados</h2>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Periodo</th>
                        <th>Generado por</th>
                        <th>Fecha</th>
                        <th>Archivo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportes as $r)
                        <tr>
                            <td>{{ str_replace('_', ' ', $r->tipo_reporte) }}</td>
                            <td>{{ $r->periodo_desde }} → {{ $r->periodo_hasta }}</td>
                            <td>{{ $r->generado_por ?? '—' }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($r->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ $r->ruta_archivo }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">Aún no hay reportes archivados. Usa el informe mensual en pantalla.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-admin-layout>
