<x-admin-layout title="Pérdidas">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-ADM-010</p>
        <h1 class="page-head__title">Historial de pérdidas</h1>
        <p class="page-head__lead">Registro trazable de mercancía dañada en descarga o bodega.</p>
    </header>

    <section class="panel">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Cant.</th>
                        <th>Origen</th>
                        <th>Motivo</th>
                        <th>Bodeguero</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($perdidas as $p)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($p->registrada_en)->format('d/m/Y H:i') }}</td>
                            <td>{{ $p->codigo }} · {{ $p->nombre }}</td>
                            <td>{{ $p->cantidad }}</td>
                            <td><span class="badge badge--warn">{{ $p->origen }}</span></td>
                            <td>{{ $p->motivo }}</td>
                            <td>{{ $p->bodeguero ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">Sin pérdidas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-admin-layout>
