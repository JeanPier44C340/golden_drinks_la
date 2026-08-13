<x-proveedor-layout title="Nueva orden">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-PRV-017 · RF-15 · RN-10</p>
        <h1 class="page-head__title">Nueva orden de entrega</h1>
        <p class="page-head__lead">Programa la entrega con al menos 24 horas de anticipación.</p>
    </header>

    <section class="panel">
        <form method="POST" action="{{ route('proveedor.ordenes.store') }}" id="form-orden">
            @csrf
            <div class="form-grid" style="margin-bottom:1rem">
                <div class="field">
                    <label for="codigo_orden">Código orden</label>
                    <input id="codigo_orden" name="codigo_orden" value="{{ old('codigo_orden', 'OE-'.now()->format('ymdHis')) }}" required>
                </div>
                <div class="field">
                    <label for="fecha_estimada">Fecha estimada</label>
                    <input id="fecha_estimada" type="datetime-local" name="fecha_estimada" value="{{ old('fecha_estimada', now()->addDay()->format('Y-m-d\TH:i')) }}" required>
                </div>
                <div class="field field--full">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" name="observaciones">{{ old('observaciones') }}</textarea>
                </div>
            </div>

            <div class="table-wrap" style="margin-bottom:1rem">
                <table class="data" id="tabla-lineas">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="linea-row">
                            <td>
                                <select name="lineas[0][producto_id]" required style="width:100%;min-height:2.2rem;padding:.4rem .5rem;background:rgba(244,244,242,.035);border:1px solid var(--line)">
                                    <option value="">Selecciona…</option>
                                    @foreach ($productos as $p)
                                        <option value="{{ $p->id }}">{{ $p->codigo }} · {{ $p->nombre }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" min="0" name="lineas[0][cantidad_programada]" value="0" required style="width:6rem;min-height:2.2rem;padding:.4rem .5rem;background:rgba(244,244,242,.035);border:1px solid var(--line)">
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div style="margin-bottom:1rem">
                <button type="button" class="btn btn-sm" id="add-linea">Agregar producto</button>
            </div>
            <div style="display:flex;gap:.7rem;flex-wrap:wrap">
                <button class="btn btn-gold" type="submit">Registrar orden</button>
                <a class="btn" href="{{ route('proveedor.entregas.index') }}">Cancelar</a>
            </div>
        </form>
    </section>

    <template id="tpl-linea">
        <tr class="linea-row">
            <td>
                <select name="lineas[__I__][producto_id]" required style="width:100%;min-height:2.2rem;padding:.4rem .5rem;background:rgba(244,244,242,.035);border:1px solid var(--line)">
                    <option value="">Selecciona…</option>
                    @foreach ($productos as $p)
                        <option value="{{ $p->id }}">{{ $p->codigo }} · {{ $p->nombre }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" min="0" name="lineas[__I__][cantidad_programada]" value="0" required style="width:6rem;min-height:2.2rem;padding:.4rem .5rem;background:rgba(244,244,242,.035);border:1px solid var(--line)">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-linea">Quitar</button>
            </td>
        </tr>
    </template>
    <script>
        (() => {
            const tbody = document.querySelector('#tabla-lineas tbody');
            const tpl = document.getElementById('tpl-linea');
            let idx = 1;
            document.getElementById('add-linea')?.addEventListener('click', () => {
                tbody.insertAdjacentHTML('beforeend', tpl.innerHTML.replaceAll('__I__', String(idx++)));
            });
            tbody.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-linea')) {
                    if (tbody.querySelectorAll('.linea-row').length > 1) e.target.closest('tr').remove();
                }
            });
        })();
    </script>
</x-proveedor-layout>
