<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FacturacionController extends Controller
{
    public function index(): View
    {
        $proveedorId = Auth::guard('proveedor')->id();

        $reportes = DB::table('reportes_generados')
            ->where('tipo_reporte', 'facturacion_proveedor')
            ->where('proveedor_id', $proveedorId)
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('proveedor.facturacion.index', compact('reportes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'periodo_desde' => ['required', 'date'],
            'periodo_hasta' => ['required', 'date', 'after_or_equal:periodo_desde'],
        ]);

        $proveedorId = Auth::guard('proveedor')->id();
        $proveedor = Auth::guard('proveedor')->user();

        $lineas = DB::table('detalle_descarga as dd')
            ->join('descargas as d', 'd.id', '=', 'dd.descarga_id')
            ->join('recepciones as r', 'r.id', '=', 'd.recepcion_id')
            ->join('productos as p', 'p.id', '=', 'dd.producto_id')
            ->where('r.proveedor_id', $proveedorId)
            ->whereDate('d.confirmada_en', '>=', $data['periodo_desde'])
            ->whereDate('d.confirmada_en', '<=', $data['periodo_hasta'])
            ->orderBy('d.confirmada_en')
            ->get([
                'r.codigo_recepcion',
                'd.confirmada_en',
                'p.codigo',
                'p.nombre',
                'p.precio_compra',
                'dd.cantidad_recibida',
                'dd.cantidad_danada',
            ]);

        $totales = [
            'recibidas' => (int) $lineas->sum('cantidad_recibida'),
            'danadas' => (int) $lineas->sum('cantidad_danada'),
            'buenas' => (int) $lineas->sum(fn ($l) => max(0, $l->cantidad_recibida - $l->cantidad_danada)),
            'valor_estimado' => (float) $lineas->sum(fn ($l) => max(0, $l->cantidad_recibida - $l->cantidad_danada) * (float) $l->precio_compra),
        ];

        $html = view('proveedor.facturacion.reporte', [
            'proveedor' => $proveedor,
            'periodo_desde' => $data['periodo_desde'],
            'periodo_hasta' => $data['periodo_hasta'],
            'lineas' => $lineas,
            'totales' => $totales,
            'generado_en' => now(),
        ])->render();

        $filename = 'facturacion-prov'.$proveedorId.'-'.now()->format('YmdHis').'.html';
        $path = 'reportes/'.$filename;
        Storage::disk('public')->put($path, $html);

        $reporteId = DB::table('reportes_generados')->insertGetId([
            'tipo_reporte' => 'facturacion_proveedor',
            'periodo_desde' => $data['periodo_desde'],
            'periodo_hasta' => $data['periodo_hasta'],
            'usuario_generador_id' => null,
            'proveedor_id' => $proveedorId,
            'vendedor_id' => null,
            'ruta_archivo' => '/storage/'.$path,
        ]);

        return redirect()
            ->route('proveedor.facturacion.download', $reporteId)
            ->with('status', $lineas->isEmpty()
                ? 'Sin entregas registradas en el período. Se generó el resumen en cero.'
                : 'Resumen de facturación generado.');
    }

    public function download(int $reporte): StreamedResponse|RedirectResponse
    {
        $proveedorId = Auth::guard('proveedor')->id();

        $row = DB::table('reportes_generados')
            ->where('id', $reporte)
            ->where('tipo_reporte', 'facturacion_proveedor')
            ->where('proveedor_id', $proveedorId)
            ->first();

        abort_unless($row, 404);

        $relative = str_replace('/storage/', '', $row->ruta_archivo);
        if (! Storage::disk('public')->exists($relative)) {
            return back()->withErrors(['reporte' => 'El archivo del reporte no está disponible.']);
        }

        return Storage::disk('public')->download($relative, basename($relative));
    }
}
