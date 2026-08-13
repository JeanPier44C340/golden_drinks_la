<?php

namespace App\Http\Controllers\Bodeguero;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PerdidaController extends Controller
{
    public function index(): View
    {
        $perdidas = DB::table('perdidas as pe')
            ->join('productos as p', 'p.id', '=', 'pe.producto_id')
            ->leftJoin('recepciones as r', 'r.id', '=', 'pe.recepcion_id')
            ->where('pe.bodeguero_id', auth()->id())
            ->orderByDesc('pe.id')
            ->get([
                'pe.id',
                'pe.cantidad',
                'pe.motivo',
                'pe.origen',
                'pe.evidencia_url',
                'pe.registrada_en',
                'p.codigo',
                'p.nombre',
                'r.codigo_recepcion',
            ]);

        return view('bodeguero.perdidas.index', compact('perdidas'));
    }

    public function create(): View
    {
        $productos = DB::table('productos')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre']);

        $recepciones = DB::table('recepciones')
            ->whereIn('estado', ['descargada', 'pendiente', 'salida'])
            ->orderByDesc('id')
            ->limit(40)
            ->get(['id', 'codigo_recepcion', 'estado']);

        return view('bodeguero.perdidas.create', compact('productos', 'recepciones'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'motivo' => ['required', 'string', 'max:180'],
            'recepcion_id' => ['nullable', 'exists:recepciones,id'],
            'evidencia' => ['required', 'image', 'max:5120'],
        ]);

        $stock = DB::table('inventario')->where('producto_id', $data['producto_id'])->value('stock_actual');
        if ($stock === null || (int) $stock < (int) $data['cantidad']) {
            return back()
                ->withErrors(['cantidad' => 'No hay stock suficiente para registrar esta pérdida.'])
                ->withInput();
        }

        $path = $request->file('evidencia')->store('perdidas', 'public');
        $evidenciaUrl = Storage::url($path);

        $producto = DB::table('productos')->where('id', $data['producto_id'])->first(['nombre', 'codigo']);

        $proveedorId = null;
        if (! empty($data['recepcion_id'])) {
            $proveedorId = DB::table('recepciones')->where('id', $data['recepcion_id'])->value('proveedor_id');
        }

        DB::transaction(function () use ($request, $data, $evidenciaUrl, $producto, $proveedorId) {
            DB::table('perdidas')->insert([
                'recepcion_id' => $data['recepcion_id'] ?? null,
                'producto_id' => $data['producto_id'],
                'bodeguero_id' => $request->user()->id,
                'origen' => 'bodega',
                'cantidad' => $data['cantidad'],
                'motivo' => $data['motivo'],
                'evidencia_url' => $evidenciaUrl,
            ]);

            if ($proveedorId) {
                DB::table('notificaciones')->insert([
                    'destinatario_tipo' => 'proveedor',
                    'destinatario_id' => $proveedorId,
                    'canal' => 'portal',
                    'tipo_evento' => 'danos_bodega',
                    'titulo' => 'Daño registrado en bodega',
                    'mensaje' => 'Se registraron '.$data['cantidad'].' unidades dañadas de '.($producto->nombre ?? 'producto').' en bodega.',
                    'referencia_tipo' => 'recepcion',
                    'referencia_id' => $data['recepcion_id'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('bodeguero.perdidas.index')
            ->with('status', 'Daño en bodega registrado. El inventario se actualizó.');
    }
}
