<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PerdidaController extends Controller
{
    public function index(): View
    {
        $perdidas = DB::table('perdidas as pe')
            ->join('productos as p', 'p.id', '=', 'pe.producto_id')
            ->leftJoin('usuarios as u', 'u.id', '=', 'pe.bodeguero_id')
            ->orderByDesc('pe.id')
            ->get([
                'pe.id',
                'pe.cantidad',
                'pe.motivo',
                'pe.origen',
                'pe.registrada_en',
                'p.codigo',
                'p.nombre',
                'u.nombre_completo as bodeguero',
            ]);

        return view('admin.perdidas.index', compact('perdidas'));
    }
}
