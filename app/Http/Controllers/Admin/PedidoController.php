<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PedidoController extends Controller
{
    public function index(): View
    {
        $pedidos = DB::table('pedidos as pe')
            ->join('vendedores as v', 'v.id', '=', 'pe.vendedor_id')
            ->orderByDesc('pe.id')
            ->get([
                'pe.id',
                'pe.codigo_pedido',
                'pe.estado',
                'pe.pago_estado',
                'pe.fecha_pedido',
                'v.empresa',
                'v.nombre_contacto',
            ]);

        return view('admin.pedidos.index', compact('pedidos'));
    }

    public function show(int $pedido): View
    {
        $pedidoRow = DB::table('pedidos as pe')
            ->join('vendedores as v', 'v.id', '=', 'pe.vendedor_id')
            ->where('pe.id', $pedido)
            ->first([
                'pe.*',
                'v.empresa',
                'v.nombre_contacto',
                'v.correo as vendedor_correo',
                'v.telefono as vendedor_telefono',
            ]);

        abort_unless($pedidoRow, 404);

        $detalle = DB::table('detalle_pedido as d')
            ->join('productos as p', 'p.id', '=', 'd.producto_id')
            ->where('d.pedido_id', $pedido)
            ->get([
                'd.cantidad_solicitada',
                'd.precio_unitario',
                'd.subtotal',
                'p.codigo',
                'p.nombre',
            ]);

        $pagos = DB::table('pedido_pago_archivos')
            ->where('pedido_id', $pedido)
            ->orderByDesc('id')
            ->get();

        return view('admin.pedidos.show', [
            'pedido' => $pedidoRow,
            'detalle' => $detalle,
            'pagos' => $pagos,
        ]);
    }

    public function verificarPago(Request $request, int $pedido): RedirectResponse
    {
        $exists = DB::table('pedidos')->where('id', $pedido)->exists();
        abort_unless($exists, 404);

        DB::table('pedidos')->where('id', $pedido)->update([
            'pago_estado' => 'verificado',
            'pago_verificado_por' => $request->user()->id,
        ]);

        return back()->with('status', 'Pago verificado.');
    }

    public function aprobar(Request $request, int $pedido): RedirectResponse
    {
        $row = DB::table('pedidos')->where('id', $pedido)->first();
        abort_unless($row, 404);

        if ($row->pago_estado !== 'verificado') {
            return back()->withErrors(['pedido' => 'Debes verificar el comprobante de pago antes de aprobar.']);
        }

        DB::table('pedidos')->where('id', $pedido)->update([
            'estado' => 'aprobado',
            'admin_aprobador_id' => $request->user()->id,
        ]);

        return back()->with('status', 'Pedido aprobado.');
    }

    public function rechazar(Request $request, int $pedido): RedirectResponse
    {
        $exists = DB::table('pedidos')->where('id', $pedido)->exists();
        abort_unless($exists, 404);

        DB::table('pedidos')->where('id', $pedido)->update([
            'estado' => 'rechazado',
            'admin_aprobador_id' => $request->user()->id,
        ]);

        return back()->with('status', 'Pedido rechazado.');
    }
}
