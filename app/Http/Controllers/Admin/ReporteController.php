<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function index(): View
    {
        $reportes = DB::table('reportes_generados as r')
            ->leftJoin('usuarios as u', 'u.id', '=', 'r.usuario_generador_id')
            ->orderByDesc('r.id')
            ->limit(30)
            ->get([
                'r.id',
                'r.tipo_reporte',
                'r.periodo_desde',
                'r.periodo_hasta',
                'r.ruta_archivo',
                'r.created_at',
                'u.nombre_completo as generado_por',
            ]);

        $resumen = DB::table('vw_dashboard_operativo')->first();

        return view('admin.reportes.index', compact('reportes', 'resumen'));
    }

    public function mensuales(Request $request): View
    {
        $mes = (int) $request->query('mes', now()->month);
        $anio = (int) $request->query('anio', now()->year);

        $kpis = [
            'recepciones' => DB::table('recepciones')
                ->whereMonth('hora_llegada', $mes)
                ->whereYear('hora_llegada', $anio)
                ->count(),
            'despachos' => DB::table('despachos')
                ->whereMonth('despachado_en', $mes)
                ->whereYear('despachado_en', $anio)
                ->count(),
            'perdidas' => (int) DB::table('perdidas')
                ->whereMonth('registrada_en', $mes)
                ->whereYear('registrada_en', $anio)
                ->sum('cantidad'),
            'pedidos_aprobados' => DB::table('pedidos')
                ->where('estado', 'aprobado')
                ->whereMonth('fecha_pedido', $mes)
                ->whereYear('fecha_pedido', $anio)
                ->count(),
        ];

        $top = DB::table('detalle_despacho as dd')
            ->join('despachos as d', 'd.id', '=', 'dd.despacho_id')
            ->join('productos as p', 'p.id', '=', 'dd.producto_id')
            ->whereMonth('d.despachado_en', $mes)
            ->whereYear('d.despachado_en', $anio)
            ->groupBy('p.id', 'p.nombre', 'p.codigo')
            ->orderByDesc(DB::raw('SUM(dd.cantidad)'))
            ->limit(10)
            ->get([
                'p.codigo',
                'p.nombre',
                DB::raw('SUM(dd.cantidad) as unidades'),
            ]);

        return view('admin.reportes.mensual', compact('kpis', 'top', 'mes', 'anio'));
    }
}
