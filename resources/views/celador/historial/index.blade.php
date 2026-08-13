<x-celador-layout title="Historial">
    <header class="page-head">
        <p class="page-head__eyebrow">DA-REC-025</p>
        <h1 class="page-head__title">Historial de vehículos</h1>
        <p class="page-head__lead">Consulta llegadas, permanencia en bodega y salidas registradas.</p>
    </header>

    <form class="toolbar" method="GET" action="{{ route('celador.historial.index') }}">
        <div class="field">
            <label for="q">Buscar</label>
            <input id="q" name="q" value="{{ $q }}" placeholder="Código, placa, proveedor…">
        </div>
        <div class="field">
            <label for="situacion">Situación</label>
            <select id="situacion" name="situacion">
                <option value="">Todas</option>
                <option value="En bodega" @selected($situacion === 'En bodega')>En bodega</option>
                <option value="Salio" @selected($situacion === 'Salio')>Salió</option>
            </select>
        </div>
        <button class="btn btn-gold" type="submit">Filtrar</button>
    </form>

    <section class="panel">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Placa</th>
                        <th>Proveedor</th>
                        <th>Llegada</th>
                        <th>Salida</th>
                        <th>Situación</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recepciones as $r)
                        <tr>
                            <td><strong>{{ $r->codigo_recepcion }}</strong></td>
                            <td>{{ $r->placa }} · {{ $r->conductor }}</td>
                            <td>{{ $r->proveedor }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($r->hora_llegada)->format('d/m/Y H:i') }}</td>
                            <td>
                                {{ $r->hora_salida ? \Illuminate\Support\Carbon::parse($r->hora_salida)->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td>
                                <span class="badge {{ $r->situacion === 'En bodega' ? 'badge--warn' : 'badge--ok' }}">
                                    {{ $r->situacion }}
                                </span>
                            </td>
                            <td>
                                <a class="btn btn-sm" href="{{ route('celador.recepciones.show', $r->recepcion_id) }}">Abrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">Sin resultados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-celador-layout>
