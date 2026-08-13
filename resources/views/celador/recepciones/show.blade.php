<x-celador-layout title="Recepción {{ $recepcion->codigo_recepcion }}">
    <header class="page-head">
        <p class="page-head__eyebrow">{{ $recepcion->codigo_recepcion }}</p>
        <h1 class="page-head__title">{{ $recepcion->proveedor }}</h1>
        <p class="page-head__lead">
            {{ $recepcion->placa }} · {{ $recepcion->conductor }}
            @if ($recepcion->codigo_orden)
                · orden {{ $recepcion->codigo_orden }}
            @endif
            · estado {{ $recepcion->estado }}
        </p>
        <div class="page-head__actions">
            @unless ($recepcion->hora_salida)
                <a class="btn btn-gold" href="{{ route('celador.recepciones.salida', $recepcion->id) }}">Registrar salida</a>
            @endunless
            <a class="btn" href="{{ route('celador.bodega.index') }}">En bodega</a>
            <a class="btn" href="{{ route('celador.historial.index') }}">Historial</a>
        </div>
    </header>

    <div class="grid-2">
        <section class="panel">
            <div class="panel__head"><h2 class="panel__title">Ciclo</h2></div>
            <ul class="list-soft">
                <li>
                    <strong>Llegada</strong>
                    <span>{{ \Illuminate\Support\Carbon::parse($recepcion->hora_llegada)->format('d/m/Y H:i') }} · {{ $recepcion->celador }}</span>
                </li>
                <li>
                    <strong>Salida</strong>
                    <span>
                        @if ($recepcion->hora_salida)
                            {{ \Illuminate\Support\Carbon::parse($recepcion->hora_salida)->format('d/m/Y H:i') }} · {{ $recepcion->celador_salida }}
                        @else
                            Pendiente
                        @endif
                    </span>
                </li>
                <li>
                    <strong>Flete</strong>
                    <span>${{ number_format($recepcion->valor_flete, 0, ',', '.') }}</span>
                </li>
                @if ($recepcion->salida_observaciones)
                    <li>
                        <strong>Obs. salida</strong>
                        <span>{{ $recepcion->salida_observaciones }}</span>
                    </li>
                @endif
                @if ($recepcion->observaciones)
                    <li>
                        <strong>Observaciones</strong>
                        <span>{{ $recepcion->observaciones }}</span>
                    </li>
                @endif
            </ul>
        </section>

        <section class="panel">
            <div class="panel__head"><h2 class="panel__title">Descarga</h2><span class="panel__meta">Bodeguero</span></div>
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Recibido</th>
                            <th>Dañado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($detalle as $line)
                            <tr>
                                <td>{{ $line->codigo }} · {{ $line->nombre }}</td>
                                <td>{{ $line->cantidad_recibida }}</td>
                                <td>{{ $line->cantidad_danada }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty">Aún sin descarga del bodeguero.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-celador-layout>
