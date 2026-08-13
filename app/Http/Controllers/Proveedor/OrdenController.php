<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrdenController extends Controller
{
    public function create(): View
    {
        $productos = DB::table('productos')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre', 'unidad_medida']);

        return view('proveedor.ordenes.create', compact('productos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'codigo_orden' => ['required', 'string', 'max:40', 'unique:ordenes_entrega,codigo_orden'],
            'fecha_estimada' => ['required', 'date'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'lineas' => ['required', 'array', 'min:1'],
            'lineas.*.producto_id' => ['required', 'exists:productos,id'],
            'lineas.*.cantidad_programada' => ['required', 'integer', 'min:0'],
        ]);

        $minFecha = now()->addHours(24);
        if (strtotime($data['fecha_estimada']) < $minFecha->timestamp) {
            throw ValidationException::withMessages([
                'fecha_estimada' => 'La orden debe programarse con al menos 24 horas de anticipación (RN-10).',
            ]);
        }

        $lineas = [];
        foreach ($data['lineas'] as $index => $linea) {
            $cant = (int) $linea['cantidad_programada'];
            if ($cant <= 0) {
                continue;
            }
            $pid = (int) $linea['producto_id'];
            if (isset($lineas[$pid])) {
                throw ValidationException::withMessages([
                    "lineas.{$index}.producto_id" => 'No repitas el mismo producto.',
                ]);
            }
            $lineas[$pid] = [
                'producto_id' => $pid,
                'cantidad_programada' => $cant,
            ];
        }

        if (empty($lineas)) {
            throw ValidationException::withMessages([
                'lineas' => 'Indica al menos un producto con cantidad mayor a cero.',
            ]);
        }

        $proveedorId = Auth::guard('proveedor')->id();

        DB::transaction(function () use ($data, $lineas, $proveedorId) {
            $ordenId = DB::table('ordenes_entrega')->insertGetId([
                'proveedor_id' => $proveedorId,
                'codigo_orden' => $data['codigo_orden'],
                'fecha_estimada' => $data['fecha_estimada'],
                'estado' => 'programada',
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            foreach ($lineas as $linea) {
                DB::table('detalle_orden_entrega')->insert([
                    'orden_entrega_id' => $ordenId,
                    'producto_id' => $linea['producto_id'],
                    'cantidad_programada' => $linea['cantidad_programada'],
                ]);
            }
        });

        return redirect()
            ->route('proveedor.entregas.index')
            ->with('status', 'Orden registrada. El celador será notificado el día de la entrega.');
    }
}
