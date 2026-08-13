<?php

namespace App\Http\Controllers\Celador;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RecepcionController extends Controller
{
    public function bodega(): View
    {
        $recepciones = DB::table('vw_ciclo_vehiculos')
            ->where('situacion', 'En bodega')
            ->orderByDesc('hora_llegada')
            ->get();

        return view('celador.bodega.index', compact('recepciones'));
    }

    public function show(int $recepcion): View
    {
        $row = $this->findRecepcion($recepcion);

        $detalle = DB::table('detalle_descarga as dd')
            ->join('descargas as d', 'd.id', '=', 'dd.descarga_id')
            ->join('productos as p', 'p.id', '=', 'dd.producto_id')
            ->where('d.recepcion_id', $recepcion)
            ->get([
                'p.codigo',
                'p.nombre',
                'dd.cantidad_recibida',
                'dd.cantidad_danada',
                'dd.motivo_dano',
            ]);

        return view('celador.recepciones.show', [
            'recepcion' => $row,
            'detalle' => $detalle,
        ]);
    }

    public function salidaForm(int $recepcion): View
    {
        $row = $this->findRecepcion($recepcion);

        if ($row->hora_salida) {
            abort(422, 'Esta recepción ya tiene salida registrada.');
        }

        return view('celador.recepciones.salida', ['recepcion' => $row]);
    }

    public function salidaStore(Request $request, int $recepcion): RedirectResponse
    {
        $row = $this->findRecepcion($recepcion);

        if ($row->hora_salida) {
            return back()->withErrors(['salida' => 'Esta recepción ya tiene salida registrada.']);
        }

        $data = $request->validate([
            'salida_observaciones' => ['nullable', 'string', 'max:180'],
        ]);

        if ($row->estado === 'pendiente') {
            // Aviso operativo: lo ideal es que el bodeguero haya descargado antes.
            // El trigger permite salida y fuerza estado=salida al setear hora_salida.
        }

        DB::table('recepciones')->where('id', $recepcion)->update([
            'hora_salida' => now(),
            'celador_salida_id' => $request->user()->id,
            'salida_observaciones' => $data['salida_observaciones'] ?? null,
        ]);

        $mensaje = 'Salida registrada.';
        if ($row->estado === 'pendiente') {
            $mensaje .= ' Nota: la recepción aún no estaba marcada como descargada.';
        }

        return redirect()
            ->route('celador.recepciones.show', $recepcion)
            ->with('status', $mensaje);
    }

    private function findRecepcion(int $id): object
    {
        $row = DB::table('recepciones as r')
            ->join('vehiculos as v', 'v.id', '=', 'r.vehiculo_id')
            ->join('proveedores as p', 'p.id', '=', 'r.proveedor_id')
            ->leftJoin('usuarios as c', 'c.id', '=', 'r.celador_id')
            ->leftJoin('usuarios as cs', 'cs.id', '=', 'r.celador_salida_id')
            ->leftJoin('ordenes_entrega as o', 'o.id', '=', 'r.orden_entrega_id')
            ->where('r.id', $id)
            ->first([
                'r.*',
                'v.placa',
                'v.conductor',
                'v.tipo_vehiculo',
                'p.nombre as proveedor',
                'c.nombre_completo as celador',
                'cs.nombre_completo as celador_salida',
                'o.codigo_orden',
            ]);

        abort_unless($row, 404);

        return $row;
    }
}
