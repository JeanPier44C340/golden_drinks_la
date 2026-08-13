<x-bodeguero-layout title="Dañados">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-BOD-027 · RF-04</p>
        <h1 class="page-head__title">Productos dañados</h1>
        <p class="page-head__lead">Pérdidas que registraste en descarga o en bodega.</p>
        <div class="page-head__actions">
            <a class="btn btn-gold" href="{{ route('bodeguero.perdidas.create') }}">Registrar daño en bodega</a>
        </div>
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
                        <th>Recepción</th>
                        <th>Evidencia</th>
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
                            <td>{{ $p->codigo_recepcion ?? '—' }}</td>
                            <td>
                                @if ($p->evidencia_url)
                                    <a class="btn btn-sm" href="{{ $p->evidencia_url }}" target="_blank" rel="noopener">Ver foto</a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">Aún no has registrado pérdidas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-bodeguero-layout>
