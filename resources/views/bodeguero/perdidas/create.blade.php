<x-bodeguero-layout title="Registrar daño">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-BOD-027 · DA-BOD-027</p>
        <h1 class="page-head__title">Registrar daño en bodega</h1>
        <p class="page-head__lead">Descuenta stock con evidencia fotográfica. El trigger actualiza el inventario.</p>
    </header>

    <section class="panel">
        <form method="POST" action="{{ route('bodeguero.perdidas.store') }}" enctype="multipart/form-data" class="form-grid">
            @csrf
            <div class="field">
                <label for="producto_id">Producto</label>
                <select id="producto_id" name="producto_id" required>
                    <option value="">Selecciona…</option>
                    @foreach ($productos as $p)
                        <option value="{{ $p->id }}" @selected(old('producto_id') == $p->id)>
                            {{ $p->codigo }} · {{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="cantidad">Cantidad</label>
                <input id="cantidad" type="number" min="1" name="cantidad" value="{{ old('cantidad', 1) }}" required>
            </div>
            <div class="field field--full">
                <label for="motivo">Motivo</label>
                <input id="motivo" name="motivo" value="{{ old('motivo') }}" maxlength="180" required>
            </div>
            <div class="field">
                <label for="recepcion_id">Recepción (opcional)</label>
                <select id="recepcion_id" name="recepcion_id">
                    <option value="">Sin vincular</option>
                    @foreach ($recepciones as $r)
                        <option value="{{ $r->id }}" @selected(old('recepcion_id') == $r->id)>
                            {{ $r->codigo_recepcion }} · {{ $r->estado }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="evidencia">Evidencia (foto)</label>
                <input id="evidencia" type="file" name="evidencia" accept="image/*" required>
            </div>
            <div class="field field--full" style="flex-direction:row;gap:.7rem">
                <button class="btn btn-gold" type="submit">Registrar daño</button>
                <a class="btn" href="{{ route('bodeguero.perdidas.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
</x-bodeguero-layout>
