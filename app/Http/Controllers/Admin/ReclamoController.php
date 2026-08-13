<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReclamoController extends Controller
{
    public function index(): View
    {
        $reclamos = DB::table('reclamos as r')
            ->leftJoin('pedidos as pe', 'pe.id', '=', 'r.pedido_id')
            ->leftJoin('vendedores as v', 'v.id', '=', 'r.vendedor_id')
            ->orderByDesc('r.id')
            ->get([
                'r.id',
                'r.descripcion',
                'r.estado',
                'r.cantidad_afectada',
                'r.created_at',
                'pe.codigo_pedido',
                'v.empresa',
            ]);

        return view('admin.reclamos.index', compact('reclamos'));
    }

    public function show(int $reclamo): View
    {
        $row = DB::table('reclamos as r')
            ->leftJoin('pedidos as pe', 'pe.id', '=', 'r.pedido_id')
            ->leftJoin('vendedores as v', 'v.id', '=', 'r.vendedor_id')
            ->where('r.id', $reclamo)
            ->first([
                'r.*',
                'pe.codigo_pedido',
                'v.empresa',
                'v.nombre_contacto',
            ]);

        abort_unless($row, 404);

        return view('admin.reclamos.show', ['reclamo' => $row]);
    }

    public function responder(Request $request, int $reclamo): RedirectResponse
    {
        $data = $request->validate([
            'respuesta_admin' => ['required', 'string', 'max:2000'],
            'estado' => ['required', 'in:abierto,en_revision,resuelto'],
        ]);

        $exists = DB::table('reclamos')->where('id', $reclamo)->exists();
        abort_unless($exists, 404);

        DB::table('reclamos')->where('id', $reclamo)->update([
            'respuesta_admin' => $data['respuesta_admin'],
            'estado' => $data['estado'],
        ]);

        return back()->with('status', 'Reclamo actualizado.');
    }
}
