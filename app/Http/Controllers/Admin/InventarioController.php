<?php

namespace App\Http\Controllers\Admin;

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

        $movimientos = DB::table('movimientos_inventario as m')
            ->join('productos as p', 'p.id', '=', 'm.producto_id')
            ->orderByDesc('m.id')
            ->limit(20)
            ->get([
                'm.id',
                'm.tipo_movimiento',
                'm.cantidad',
                'm.saldo_anterior',
                'm.saldo_resultante',
                'm.created_at',
                'm.nota',
                'p.codigo',
                'p.nombre',
            ]);

        return view('admin.inventario.index', compact('items', 'movimientos', 'q', 'estado'));
    }
}
