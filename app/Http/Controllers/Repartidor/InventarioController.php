<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventarioController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $estado = (string) $request->query('estado', '');

        $query = DB::table('vw_inventario_estado');

        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('nombre', 'like', "%{$q}%")
                    ->orWhere('codigo', 'like', "%{$q}%")
                    ->orWhere('categoria', 'like', "%{$q}%");
            });
        }

        if (in_array($estado, ['Disponible', 'Stock Bajo', 'Agotado'], true)) {
            $query->where('estado_visual', $estado);
        }

        $items = $query->orderBy('nombre')->get();

        return view('repartidor.inventario.index', compact('items', 'q', 'estado'));
    }
}
