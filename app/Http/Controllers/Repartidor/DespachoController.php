<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DespachoController extends Controller
{
    public function index(): View
    {
        $despachos = DB::table('despachos as d')
            ->leftJoin('pedidos as pe', 'pe.id', '=', 'd.pedido_id')
            ->where('d.repartidor_id', auth()->id())
            ->orderByDesc('d.id')
            ->get([
                'd.id',
                'd.codigo_despacho',
                'd.estado',
                'd.despachado_en',
                'd.entregado_en',
                'pe.codigo_pedido',
            ]);

        return view('repartidor.despachos.index', compact('despachos'));
    }

    public function show(int $despacho): View
    {
        $row = $this->findOwn($despacho);

        $detalle = DB::table('detalle_despacho as dd')
            ->join('productos as p', 'p.id', '=', 'dd.producto_id')
            ->where('dd.despacho_id', $despacho)
            ->orderBy('p.nombre')
            ->get([
                'p.codigo',
                'p.nombre',
                'dd.cantidad',
            ]);

        $evidencia = DB::table('despacho_entrega_archivos')
            ->where('despacho_id', $despacho)
            ->orderByDesc('id')
            ->first();

        return view('repartidor.despachos.show', [
            'despacho' => $row,
            'detalle' => $detalle,
            'evidencia' => $evidencia,
        ]);
    }

    public function marcarEnCamino(int $despacho): RedirectResponse
    {
        $row = $this->findOwn($despacho);

        if ($row->estado !== 'creado') {
            return back()->withErrors(['despacho' => 'Solo se puede iniciar ruta si el despacho está creado.']);
        }

        DB::table('despachos')->where('id', $despacho)->update([
            'estado' => 'en_camino',
            'despachado_en' => now(),
        ]);

        return redirect()
            ->route('repartidor.despachos.show', $despacho)
            ->with('status', 'Despacho en camino.');
    }

    public function entregarForm(int $despacho): View
    {
        $row = $this->findOwn($despacho);

        if (! in_array($row->estado, ['creado', 'en_camino'], true)) {
            abort(422, 'Este despacho ya no admite confirmación de entrega.');
        }

        $detalle = DB::table('detalle_despacho as dd')
            ->join('productos as p', 'p.id', '=', 'dd.producto_id')
            ->where('dd.despacho_id', $despacho)
            ->orderBy('p.nombre')
            ->get([
                'p.codigo',
                'p.nombre',
                'dd.cantidad',
            ]);

        return view('repartidor.despachos.entregar', [
            'despacho' => $row,
            'detalle' => $detalle,
        ]);
    }

    public function entregarStore(Request $request, int $despacho): RedirectResponse
    {
        $row = $this->findOwn($despacho);

        if (! in_array($row->estado, ['creado', 'en_camino'], true)) {
            return back()->withErrors(['despacho' => 'Este despacho ya no admite confirmación de entrega.']);
        }

        $data = $request->validate([
            'evidencia' => ['required', 'image', 'max:5120'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $path = $request->file('evidencia')->store('entregas', 'public');
        $archivoUrl = Storage::url($path);

        if ($row->estado === 'creado') {
            DB::table('despachos')->where('id', $despacho)->update([
                'estado' => 'en_camino',
                'despachado_en' => now(),
            ]);
        }

        DB::table('despacho_entrega_archivos')->insert([
            'despacho_id' => $despacho,
            'repartidor_id' => $request->user()->id,
            'archivo_url' => $archivoUrl,
            'tipo_archivo' => 'foto_entrega',
            'latitud' => $data['latitud'] ?? null,
            'longitud' => $data['longitud'] ?? null,
        ]);

        return redirect()
            ->route('repartidor.despachos.show', $despacho)
            ->with('status', 'Entrega confirmada con evidencia fotográfica.');
    }

    private function findOwn(int $id): object
    {
        $row = DB::table('despachos as d')
            ->leftJoin('pedidos as pe', 'pe.id', '=', 'd.pedido_id')
            ->where('d.id', $id)
            ->where('d.repartidor_id', auth()->id())
            ->first([
                'd.*',
                'pe.codigo_pedido',
                'pe.estado as pedido_estado',
            ]);

        abort_unless($row, 404);

        return $row;
    }
}
