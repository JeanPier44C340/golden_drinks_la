<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $repartidorId = auth()->id();

        $asignados = DB::table('despachos')
            ->where('repartidor_id', $repartidorId)
            ->whereIn('estado', ['creado', 'en_camino'])
            ->count();

        $enCamino = DB::table('despachos')
            ->where('repartidor_id', $repartidorId)
            ->where('estado', 'en_camino')
            ->count();

        $entregadosHoy = DB::table('despachos')
            ->where('repartidor_id', $repartidorId)
            ->where('estado', 'entregado')
            ->whereDate('entregado_en', today())
            ->count();

        $stockBajo = DB::table('vw_inventario_estado')
            ->whereIn('estado_visual', ['Stock Bajo', 'Agotado'])
            ->count();

        $despachosLista = DB::table('despachos as d')
            ->leftJoin('pedidos as pe', 'pe.id', '=', 'd.pedido_id')
            ->where('d.repartidor_id', $repartidorId)
            ->whereIn('d.estado', ['creado', 'en_camino'])
            ->orderByDesc('d.id')
            ->limit(8)
            ->get([
                'd.id',
                'd.codigo_despacho',
                'd.estado',
                'd.despachado_en',
                'pe.codigo_pedido',
            ]);

        return view('repartidor.dashboard', compact(
            'asignados',
            'enCamino',
            'entregadosHoy',
            'stockBajo',
            'despachosLista'
        ));
    }
}
