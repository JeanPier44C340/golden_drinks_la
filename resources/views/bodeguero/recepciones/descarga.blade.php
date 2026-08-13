<x-bodeguero-layout title="Confirmar descarga">
    <header class="page-head">
        <p class="page-head__eyebrow">HU-BOD-003 · DA-BOD-003</p>
        <h1 class="page-head__title">Confirmar descarga</h1>
        <p class="page-head__lead">
            {{ $recepcion->codigo_recepcion }} · {{ $recepcion->placa }} · {{ $recepcion->proveedor }}
            @if ($recepcion->codigo_orden)
                · Orden {{ $recepcion->codigo_orden }}
            @endif
        </p>
    </header>

    <section class="panel">
        <form method="POST" action="{{ route('bodeguero.recepciones.descarga.store', $recepcion->id) }}" id="form-descarga">
            @csrf

            <div class="table-wrap" style="margin-bottom:1rem">
                <table class="data" id="tabla-lineas">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Recibida</th>
                            <th>Dañada</th>
                            <th>Motivo daño</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($lineas->isNotEmpty())
                            @foreach ($lineas as $i => $linea)
                                <tr class="linea-row">
                                    <td>
                                        <input type="hidden" name="lineas[{{ $i }}][producto_id]" value="{{ $linea->producto_id }}">
                                        <strong>{{ $linea->codigo }}</strong> · {{ $linea->nombre }}
                                        <div style="color:var(--muted);font-size:.8rem;margin-top:.2rem">Programado: {{ $linea->cantidad_programada }}</div>
                                    </td>
                                    <td>
                                        <input type="number" min="0" name="lineas[{{ $i }}][cantidad_recibida]" value="{{ old("lineas.$i.cantidad_recibida", $linea->cantidad_programada) }}" required style="width:5.5rem;min-height:2.2rem;padding:.4rem .5rem;background:rgba(244,244,242,.035);border:1px solid var(--line)">
                                    </td>
                                    <td>
                                        <input type="number" min="0" name="lineas[{{ $i }}][cantidad_danada]" value="{{ old("lineas.$i.cantidad_danada", 0) }}" required style="width:5.5rem;min-height:2.2rem;padding:.4rem .5rem;background:rgba(244,244,242,.035);border:1px solid var(--line)">
                                    </td>
                                    <td>
                                        <input type="text" name="lineas[{{ $i }}][motivo_dano]" value="{{ old("lineas.$i.motivo_dano") }}" maxlength="180" placeholder="Si hay dañados…" style="width:100%;min-height:2.2rem;padding:.4rem .5rem;background:rgba(244,244,242,.035);border:1px solid var(--line)">
                                    </td>
                                    <td></td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="linea-row">
                                <td>
                                    <select name="lineas[0][producto_id]" required style="width:100%;min-height:2.2rem;padding:.4rem .5rem;background:rgba(244,244,242,.035);border:1px solid var(--line)">
                                        <option value="">Selecciona…</option>
                                        @foreach ($productos as $p)
                                            <option value="{{ $p->id }}" @selected(old('lineas.0.producto_id') == $p->id)>{{ $p->codigo }} · {{ $p->nombre }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" min="0" name="lineas[0][cantidad_recibida]" value="{{ old('lineas.0.cantidad_recibida', 0) }}" required style="width:5.5rem;min-height:2.2rem;padding:.4rem .5rem;background:rgba(244,244,242,.035);border:1px solid var(--line)">
                                </td>
                                <td>
                                    <input type="number" min="0" name="lineas[0][cantidad_danada]" value="{{ old('lineas.0.cantidad_danada', 0) }}" required style="width:5.5rem;min-height:2.2rem;padding:.4rem .5rem;background:rgba(244,244,242,.035);border:1px solid var(--line)">
                                </td>
                                <td>
                                    <input type="text" name="lineas[0][motivo_dano]" value="{{ old('lineas.0.motivo_dano') }}" maxlength="180" placeholder="Si hay dañados…" style="width:100%;min-height:2.2rem;padding:.4rem .5rem;background:rgba(244,244,242,.035);border:1px solid var(--line)">
                                </td>
                                <td></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if ($lineas->isEmpty())
                <div style="margin-bottom:1rem">
                    <button type="button" class="btn btn-sm" id="add-linea">Agregar producto</button>
                </div>
            @endif

            <div class="form-grid">
                <div class="field field--full">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" name="observaciones">{{ old('observaciones') }}</textarea>
                </div>
                <div class="field field--full" style="flex-direction:row;gap:.7rem">
                    <button class="btn btn-gold" type="submit">Confirmar descarga</button>
                    <a class="btn" href="{{ route('bodeguero.pendientes.index') }}">Cancelar</a>
                </div>
            </div>
        </form>
    </section>

    @if ($lineas->isEmpty())
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
                    <input type="number" min="0" name="lineas[__I__][cantidad_recibida]" value="0" required style="width:5.5rem;min-height:2.2rem;padding:.4rem .5rem;background:rgba(244,244,242,.035);border:1px solid var(--line)">
                </td>
                <td>
                    <input type="number" min="0" name="lineas[__I__][cantidad_danada]" value="0" required style="width:5.5rem;min-height:2.2rem;padding:.4rem .5rem;background:rgba(244,244,242,.035);border:1px solid var(--line)">
                </td>
                <td>
                    <input type="text" name="lineas[__I__][motivo_dano]" maxlength="180" placeholder="Si hay dañados…" style="width:100%;min-height:2.2rem;padding:.4rem .5rem;background:rgba(244,244,242,.035);border:1px solid var(--line)">
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
                let idx = tbody.querySelectorAll('.linea-row').length;
                document.getElementById('add-linea')?.addEventListener('click', () => {
                    const html = tpl.innerHTML.replaceAll('__I__', String(idx++));
                    tbody.insertAdjacentHTML('beforeend', html);
                });
                tbody.addEventListener('click', (e) => {
                    if (e.target.classList.contains('remove-linea')) {
                        const rows = tbody.querySelectorAll('.linea-row');
                        if (rows.length > 1) e.target.closest('tr').remove();
                    }
                });
            })();
        </script>
    @endif
</x-bodeguero-layout>
