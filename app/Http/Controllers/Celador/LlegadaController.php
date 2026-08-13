<?php

namespace App\Http\Controllers\Celador;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LlegadaController extends Controller
{
    public function create(): View
    {
        $vehiculos = DB::table('vehiculos')
            ->where('estado', 'disponible')
            ->orderBy('placa')
            ->get();

        $proveedores = DB::table('proveedores')
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get();

        $ordenes = DB::table('ordenes_entrega as o')
            ->join('proveedores as p', 'p.id', '=', 'o.proveedor_id')
            ->whereIn('o.estado', ['programada', 'en_proceso'])
            ->orderByDesc('o.id')
            ->get([
                'o.id',
                'o.codigo_orden',
                'o.proveedor_id',
                'o.fecha_estimada',
                'p.nombre as proveedor',
            ]);

        return view('celador.llegadas.create', compact('vehiculos', 'proveedores', 'ordenes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vehiculo_id' => ['required', 'exists:vehiculos,id'],
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'orden_entrega_id' => ['nullable', 'exists:ordenes_entrega,id'],
            'codigo_recepcion' => ['required', 'string', 'max:40', 'unique:recepciones,codigo_recepcion'],
            'valor_flete' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! empty($data['orden_entrega_id'])) {
            $orden = DB::table('ordenes_entrega')->where('id', $data['orden_entrega_id'])->first();
            if ($orden && (int) $orden->proveedor_id !== (int) $data['proveedor_id']) {
                return back()
                    ->withErrors(['orden_entrega_id' => 'La orden no pertenece al proveedor seleccionado.'])
                    ->withInput();
            }
        }

        $id = DB::table('recepciones')->insertGetId([
            'orden_entrega_id' => $data['orden_entrega_id'] ?? null,
            'vehiculo_id' => $data['vehiculo_id'],
            'proveedor_id' => $data['proveedor_id'],
            'celador_id' => $request->user()->id,
            'codigo_recepcion' => $data['codigo_recepcion'],
            'valor_flete' => $data['valor_flete'] ?? 0,
            'observaciones' => $data['observaciones'] ?? null,
            'estado' => 'pendiente',
        ]);

        return redirect()
            ->route('celador.recepciones.show', $id)
            ->with('status', 'Llegada registrada. El vehículo quedó en bodega.');
    }
}
