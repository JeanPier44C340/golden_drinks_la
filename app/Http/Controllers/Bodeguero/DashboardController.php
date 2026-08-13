<?php

namespace App\Http\Controllers\Bodeguero;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $pendientes = DB::table('recepciones')
            ->where('estado', 'pendiente')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('descargas')
                    ->whereColumn('descargas.recepcion_id', 'recepciones.id');
            })
            ->count();

        $descargasHoy = DB::table('descargas')
            ->whereDate('confirmada_en', today())
            ->count();

        $stockBajo = DB::table('vw_inventario_estado')
            ->whereIn('estado_visual', ['Stock Bajo', 'Agotado'])
            ->count();

        $perdidasHoy = DB::table('perdidas')
            ->whereDate('registrada_en', today())
            ->count();

        $pendientesLista = DB::table('recepciones as r')
            ->join('vehiculos as v', 'v.id', '=', 'r.vehiculo_id')
            ->join('proveedores as p', 'p.id', '=', 'r.proveedor_id')
            ->where('r.estado', 'pendiente')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('descargas as d')
                    ->whereColumn('d.recepcion_id', 'r.id');
            })
            ->orderByDesc('r.hora_llegada')
            ->limit(8)
            ->get([
                'r.id',
                'r.codigo_recepcion',
                'r.hora_llegada',
                'r.estado',
                'v.placa',
                'v.conductor',
                'p.nombre as proveedor',
            ]);

        return view('bodeguero.dashboard', compact(
            'pendientes',
            'descargasHoy',
            'stockBajo',
            'perdidasHoy',
            'pendientesLista'
        ));
    }
}
