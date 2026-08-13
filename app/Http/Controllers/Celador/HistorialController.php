<?php

namespace App\Http\Controllers\Celador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HistorialController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $situacion = (string) $request->query('situacion', '');

        $query = DB::table('vw_ciclo_vehiculos');

        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('codigo_recepcion', 'like', "%{$q}%")
                    ->orWhere('placa', 'like', "%{$q}%")
                    ->orWhere('conductor', 'like', "%{$q}%")
                    ->orWhere('proveedor', 'like', "%{$q}%");
            });
        }

        if (in_array($situacion, ['En bodega', 'Salio'], true)) {
            $query->where('situacion', $situacion);
        }

        $recepciones = $query->orderByDesc('recepcion_id')->limit(100)->get();

        return view('celador.historial.index', compact('recepciones', 'q', 'situacion'));
    }
}
