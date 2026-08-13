<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DespachoController extends Controller
{
    public function index(): View
    {
        $despachos = DB::table('despachos as d')
            ->leftJoin('pedidos as pe', 'pe.id', '=', 'd.pedido_id')
            ->join('usuarios as a', 'a.id', '=', 'd.admin_id')
            ->join('usuarios as r', 'r.id', '=', 'd.repartidor_id')
            ->orderByDesc('d.id')
            ->get([
                'd.id',
                'd.codigo_despacho',
                'd.estado',
                'd.despachado_en',
                'pe.codigo_pedido',
                'a.nombre_completo as admin',
                'r.nombre_completo as repartidor',
            ]);

        return view('admin.despachos.index', compact('despachos'));
    }

    public function create(): View
    {
        $pedidos = DB::table('pedidos')
            ->where('estado', 'aprobado')
            ->orderByDesc('id')
            ->get(['id', 'codigo_pedido']);

        $repartidores = DB::table('usuarios as u')
            ->join('roles as r', 'r.id', '=', 'u.rol_id')
            ->where('r.nombre', 'repartidor')
            ->where('u.estado', 'activo')
            ->get(['u.id', 'u.nombre_completo']);

        $productos = DB::table('vw_inventario_estado')
            ->where('stock_actual', '>', 0)
            ->orderBy('nombre')
            ->get();

        return view('admin.despachos.create', compact('pedidos', 'repartidores', 'productos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pedido_id' => ['nullable', 'exists:pedidos,id'],
            'repartidor_id' => ['required', 'exists:usuarios,id'],
            'codigo_despacho' => ['required', 'string', 'max:40', 'unique:despachos,codigo_despacho'],
            'productos' => ['required', 'array', 'min:1'],
            'productos.*.producto_id' => ['required', 'exists:productos,id'],
            'productos.*.cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $lines = collect($data['productos'])
            ->filter(fn (array $line) => (int) $line['cantidad'] > 0)
            ->values();

        if ($lines->isEmpty()) {
            return back()->withErrors(['productos' => 'Indica al menos un producto con cantidad.'])->withInput();
        }

        DB::transaction(function () use ($data, $request, $lines) {
            $despachoId = DB::table('despachos')->insertGetId([
                'pedido_id' => $data['pedido_id'] ?? null,
                'admin_id' => $request->user()->id,
                'repartidor_id' => $data['repartidor_id'],
                'codigo_despacho' => $data['codigo_despacho'],
                'estado' => 'creado',
            ]);

            foreach ($lines as $line) {
                DB::table('detalle_despacho')->insert([
                    'despacho_id' => $despachoId,
                    'producto_id' => $line['producto_id'],
                    'cantidad' => $line['cantidad'],
                ]);
            }
        });

        return redirect()->route('admin.despachos.index')->with('status', 'Despacho creado.');
    }

    public function cancelar(int $despacho): RedirectResponse
    {
        $row = DB::table('despachos')->where('id', $despacho)->first();
        abort_unless($row, 404);

        if (in_array($row->estado, ['entregado', 'cancelado'], true)) {
            return back()->withErrors(['despacho' => 'No se puede cancelar este despacho.']);
        }

        DB::table('despachos')->where('id', $despacho)->update(['estado' => 'cancelado']);

        return back()->with('status', 'Despacho cancelado.');
    }
}
