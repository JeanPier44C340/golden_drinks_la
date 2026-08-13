<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $kpi = DB::table('vw_dashboard_operativo')->first();
        $estrella = DB::table('vw_producto_estrella_mes')->first();
        $alertas = DB::table('alertas_stock as a')
            ->join('productos as p', 'p.id', '=', 'a.producto_id')
            ->where('a.estado', 'abierta')
            ->orderByDesc('a.id')
            ->limit(8)
            ->get([
                'a.id',
                'a.stock_detectado',
                'a.stock_minimo',
                'a.abierta_en',
                'p.codigo',
                'p.nombre',
            ]);
        $inventarioCritico = DB::table('vw_inventario_estado')
            ->whereIn('estado_visual', ['Agotado', 'Stock Bajo'])
            ->orderBy('stock_actual')
            ->limit(6)
            ->get();
        $pedidosPendientes = DB::table('pedidos as pe')
            ->join('vendedores as v', 'v.id', '=', 'pe.vendedor_id')
            ->where('pe.estado', 'en_revision')
            ->orderByDesc('pe.id')
            ->limit(5)
            ->get([
                'pe.id',
                'pe.codigo_pedido',
                'pe.pago_estado',
                'pe.fecha_pedido',
                'v.empresa',
            ]);

        return view('admin.dashboard', [
            'kpi' => $kpi,
            'estrella' => $estrella,
            'alertas' => $alertas,
            'inventarioCritico' => $inventarioCritico,
            'pedidosPendientes' => $pedidosPendientes,
        ]);
    }
}
