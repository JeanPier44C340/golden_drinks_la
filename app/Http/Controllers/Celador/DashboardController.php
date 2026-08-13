<?php

namespace App\Http\Controllers\Celador;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $llegadasHoy = DB::table('recepciones')
            ->whereDate('hora_llegada', today())
            ->count();

        $enBodega = DB::table('recepciones')
            ->whereNull('hora_salida')
            ->whereNotIn('estado', ['cancelada', 'salida'])
            ->count();

        $salidasHoy = DB::table('recepciones')
            ->whereDate('hora_salida', today())
            ->count();

        $pendientesSalida = DB::table('recepciones')
            ->whereNull('hora_salida')
            ->where('estado', 'descargada')
            ->count();

        $enBodegaLista = DB::table('vw_ciclo_vehiculos')
            ->where('situacion', 'En bodega')
            ->orderByDesc('hora_llegada')
            ->limit(8)
            ->get();

        return view('celador.dashboard', compact(
            'llegadasHoy',
            'enBodega',
            'salidasHoy',
            'pendientesSalida',
            'enBodegaLista'
        ));
    }
}
