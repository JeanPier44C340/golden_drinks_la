<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EntregaController extends Controller
{
    public function index(Request $request): View
    {
        $proveedorId = Auth::guard('proveedor')->id();
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $query = DB::table('recepciones as r')
            ->join('vehiculos as v', 'v.id', '=', 'r.vehiculo_id')
            ->leftJoin('ordenes_entrega as o', 'o.id', '=', 'r.orden_entrega_id')
            ->where('r.proveedor_id', $proveedorId);

        if ($desde) {
            $query->whereDate('r.hora_llegada', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('r.hora_llegada', '<=', $hasta);
        }

        $entregas = $query->orderByDesc('r.hora_llegada')->get([
            'r.id',
            'r.codigo_recepcion',
            'r.hora_llegada',
            'r.estado',
            'v.placa',
            'v.conductor',
            'o.codigo_orden',
        ]);

        $noLeidas = DB::table('notificaciones')
            ->where('destinatario_tipo', 'proveedor')
            ->where('destinatario_id', $proveedorId)
            ->where('leida', false)
            ->count();

        return view('proveedor.entregas.index', compact('entregas', 'desde', 'hasta', 'noLeidas'));
    }

    public function show(int $entrega): View
    {
        $proveedorId = Auth::guard('proveedor')->id();

        $row = DB::table('recepciones as r')
            ->join('vehiculos as v', 'v.id', '=', 'r.vehiculo_id')
            ->leftJoin('ordenes_entrega as o', 'o.id', '=', 'r.orden_entrega_id')
            ->where('r.id', $entrega)
            ->where('r.proveedor_id', $proveedorId)
            ->first([
                'r.*',
                'v.placa',
                'v.conductor',
                'v.tipo_vehiculo',
                'o.codigo_orden',
            ]);

        abort_unless($row, 404);

        $detalle = DB::table('detalle_descarga as dd')
            ->join('descargas as d', 'd.id', '=', 'dd.descarga_id')
            ->join('productos as p', 'p.id', '=', 'dd.producto_id')
            ->leftJoin('usuarios as u', 'u.id', '=', 'd.bodeguero_id')
            ->where('d.recepcion_id', $entrega)
            ->get([
                'p.codigo',
                'p.nombre',
                'dd.cantidad_recibida',
                'dd.cantidad_danada',
                'dd.motivo_dano',
                'u.nombre_completo as bodeguero',
                'd.confirmada_en',
            ]);

        return view('proveedor.entregas.show', [
            'entrega' => $row,
            'detalle' => $detalle,
        ]);
    }
}
