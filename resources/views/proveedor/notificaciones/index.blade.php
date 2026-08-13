<x-proveedor-layout title="Notificaciones">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-PRV-016 · RF-14</p>
        <h1 class="page-head__title">Notificaciones</h1>
        <p class="page-head__lead">
            Avisos de daños y novedades de tus entregas.
            @if ($pendientes > 0)
                Tienes {{ $pendientes }} sin revisar.
            @endif
        </p>
    </header>

    <section class="panel">
        <ul class="list-soft">
            @forelse ($notificaciones as $n)
                <li>
                    <strong>{{ $n->titulo }}</strong>
                    <span>{{ \Illuminate\Support\Carbon::parse($n->created_at)->format('d/m/Y H:i') }} · {{ $n->tipo_evento }} · {{ $n->leida ? 'Leída' : 'Pendiente' }}</span>
                    <span>{{ $n->mensaje }}</span>
                    @if (! $n->leida)
                        <form method="POST" action="{{ route('proveedor.notificaciones.leer', $n->id) }}" style="margin-top:.45rem">
                            @csrf
                            <button class="btn btn-sm btn-gold" type="submit">Abrir / marcar leída</button>
                        </form>
                    @elseif ($n->referencia_tipo === 'recepcion' && $n->referencia_id)
                        <a class="btn btn-sm" style="margin-top:.45rem;display:inline-flex" href="{{ route('proveedor.entregas.show', $n->referencia_id) }}">Ver entrega</a>
                    @endif
                </li>
            @empty
                <li class="empty">No hay notificaciones.</li>
            @endforelse
        </ul>
    </section>
</x-proveedor-layout>
