<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RecepcionController extends Controller
{
    public function index(): View
    {
        $recepciones = DB::table('vw_ciclo_vehiculos')
            ->orderByDesc('recepcion_id')
            ->get();

        return view('admin.recepciones.index', compact('recepciones'));
    }

    public function show(int $recepcion): View
    {
        $row = DB::table('recepciones as r')
            ->join('vehiculos as v', 'v.id', '=', 'r.vehiculo_id')
            ->join('proveedores as p', 'p.id', '=', 'r.proveedor_id')
            ->leftJoin('usuarios as c', 'c.id', '=', 'r.celador_id')
            ->where('r.id', $recepcion)
            ->first([
                'r.*',
                'v.placa',
                'v.conductor',
                'p.nombre as proveedor',
                'c.nombre_completo as celador',
            ]);

        abort_unless($row, 404);

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

        return view('admin.recepciones.show', [
            'recepcion' => $row,
            'detalle' => $detalle,
        ]);
    }
}
