<x-celador-layout title="Registrar llegada">
    <header class="page-head">
        <p class="page-head__eyebrow">DA-REC-001 · RF-01</p>
        <h1 class="page-head__title">Registrar llegada</h1>
        <p class="page-head__lead">Ingresa el vehículo del proveedor y deja la recepción en estado pendiente de descarga.</p>
    </header>

    <section class="panel">
        <form method="POST" action="{{ route('celador.llegadas.store') }}" class="form-grid">
            @csrf
            <div class="field">
                <label for="codigo_recepcion">Código recepción</label>
                <input id="codigo_recepcion" name="codigo_recepcion" value="{{ old('codigo_recepcion', 'REC-'.now()->format('ymdHis')) }}" required>
            </div>
            <div class="field">
                <label for="valor_flete">Valor flete</label>
                <input id="valor_flete" type="number" min="0" step="0.01" name="valor_flete" value="{{ old('valor_flete', 0) }}">
            </div>
            <div class="field">
                <label for="vehiculo_id">Vehículo</label>
                <select id="vehiculo_id" name="vehiculo_id" required>
                    <option value="">Selecciona…</option>
                    @foreach ($vehiculos as $v)
                        <option value="{{ $v->id }}" @selected(old('vehiculo_id') == $v->id)>
                            {{ $v->placa }} · {{ $v->conductor }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="proveedor_id">Proveedor</label>
                <select id="proveedor_id" name="proveedor_id" required>
                    <option value="">Selecciona…</option>
                    @foreach ($proveedores as $p)
                        <option value="{{ $p->id }}" @selected(old('proveedor_id') == $p->id)>{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field field--full">
                <label for="orden_entrega_id">Orden de entrega (opcional)</label>
                <select id="orden_entrega_id" name="orden_entrega_id">
                    <option value="">Sin orden</option>
                    @foreach ($ordenes as $o)
                        <option
                            value="{{ $o->id }}"
                            data-proveedor="{{ $o->proveedor_id }}"
                            @selected(old('orden_entrega_id') == $o->id)
                        >
                            {{ $o->codigo_orden }} · {{ $o->proveedor }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field field--full">
                <label for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones">{{ old('observaciones') }}</textarea>
            </div>
            <div class="field field--full" style="flex-direction:row;gap:.7rem">
                <button class="btn btn-gold" type="submit">Registrar llegada</button>
                <a class="btn" href="{{ route('celador.dashboard') }}">Cancelar</a>
            </div>
        </form>
    </section>
</x-celador-layout>
