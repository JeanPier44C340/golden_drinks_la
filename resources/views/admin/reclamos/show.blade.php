<x-admin-layout title="Reclamo #{{ $reclamo->id }}">
    <header class="page-head">
        <p class="page-head__eyebrow">Reclamo · {{ $reclamo->codigo_pedido }}</p>
        <h1 class="page-head__title">{{ $reclamo->empresa }}</h1>
        <p class="page-head__lead">{{ $reclamo->nombre_contacto }} · {{ $reclamo->cantidad_afectada }} unidades afectadas</p>
    </header>

    <div class="grid-2">
        <section class="panel">
            <div class="panel__head"><h2 class="panel__title">Descripción</h2></div>
            <p style="font-weight:300;line-height:1.6;color:var(--muted)">{{ $reclamo->descripcion }}</p>
            @if ($reclamo->respuesta_admin)
                <p style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--faint);font-weight:300">
                    <strong style="color:var(--gold-soft)">Respuesta:</strong><br>
                    {{ $reclamo->respuesta_admin }}
                </p>
            @endif
        </section>

        <section class="panel">
            <div class="panel__head"><h2 class="panel__title">Responder</h2></div>
            <form method="POST" action="{{ route('admin.reclamos.responder', $reclamo->id) }}" class="form-grid">
                @csrf
                <div class="field field--full">
                    <label for="respuesta_admin">Respuesta</label>
                    <textarea id="respuesta_admin" name="respuesta_admin" required>{{ old('respuesta_admin', $reclamo->respuesta_admin) }}</textarea>
                </div>
                <div class="field">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado" required>
                        @foreach (['abierto','en_revision','resuelto'] as $estado)
                            <option value="{{ $estado }}" @selected(old('estado', $reclamo->estado) === $estado)>{{ str_replace('_',' ', $estado) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field" style="justify-content:end">
                    <label>&nbsp;</label>
                    <button class="btn btn-gold" type="submit">Guardar</button>
                </div>
            </form>
        </section>
    </div>
</x-admin-layout>
