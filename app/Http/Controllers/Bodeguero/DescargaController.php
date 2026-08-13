<?php

namespace App\Http\Controllers\Bodeguero;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DescargaController extends Controller
{
    public function pendientes(): View
    {
        $recepciones = DB::table('recepciones as r')
            ->join('vehiculos as v', 'v.id', '=', 'r.vehiculo_id')
            ->join('proveedores as p', 'p.id', '=', 'r.proveedor_id')
            ->leftJoin('ordenes_entrega as o', 'o.id', '=', 'r.orden_entrega_id')
            ->where('r.estado', 'pendiente')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('descargas as d')
                    ->whereColumn('d.recepcion_id', 'r.id');
            })
            ->orderByDesc('r.hora_llegada')
            ->get([
                'r.id',
                'r.codigo_recepcion',
                'r.hora_llegada',
                'r.estado',
                'v.placa',
                'v.conductor',
                'p.nombre as proveedor',
                'o.codigo_orden',
            ]);

        return view('bodeguero.pendientes.index', compact('recepciones'));
    }

    public function create(int $recepcion): View
    {
        $row = $this->findPendiente($recepcion);

        $lineas = collect();
        if ($row->orden_entrega_id) {
            $lineas = DB::table('detalle_orden_entrega as doe')
                ->join('productos as p', 'p.id', '=', 'doe.producto_id')
                ->where('doe.orden_entrega_id', $row->orden_entrega_id)
                ->orderBy('p.nombre')
                ->get([
                    'p.id as producto_id',
                    'p.codigo',
                    'p.nombre',
                    'doe.cantidad_programada',
                ]);
        }

        $productos = DB::table('productos')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre']);

        return view('bodeguero.recepciones.descarga', [
            'recepcion' => $row,
            'lineas' => $lineas,
            'productos' => $productos,
        ]);
    }

    public function store(Request $request, int $recepcion): RedirectResponse
    {
        $row = $this->findPendiente($recepcion);

        $data = $request->validate([
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'lineas' => ['required', 'array', 'min:1'],
            'lineas.*.producto_id' => ['required', 'exists:productos,id'],
            'lineas.*.cantidad_recibida' => ['required', 'integer', 'min:0'],
            'lineas.*.cantidad_danada' => ['required', 'integer', 'min:0'],
            'lineas.*.motivo_dano' => ['nullable', 'string', 'max:180'],
        ]);

        $lineasValidas = [];
        $totalRecibida = 0;
        $danosResumen = [];

        foreach ($data['lineas'] as $index => $linea) {
            $recibida = (int) $linea['cantidad_recibida'];
            $danada = (int) $linea['cantidad_danada'];

            if ($recibida === 0 && $danada === 0) {
                continue;
            }

            if ($danada > $recibida) {
                throw ValidationException::withMessages([
                    "lineas.{$index}.cantidad_danada" => 'La cantidad dañada no puede superar la recibida (RN-04).',
                ]);
            }

            if ($danada > 0 && blank($linea['motivo_dano'] ?? null)) {
                throw ValidationException::withMessages([
                    "lineas.{$index}.motivo_dano" => 'El motivo es obligatorio cuando hay unidades dañadas.',
                ]);
            }

            $productoId = (int) $linea['producto_id'];
            if (isset($lineasValidas[$productoId])) {
                throw ValidationException::withMessages([
                    "lineas.{$index}.producto_id" => 'No repitas el mismo producto en la descarga.',
                ]);
            }

            $lineasValidas[$productoId] = [
                'producto_id' => $productoId,
                'cantidad_recibida' => $recibida,
                'cantidad_danada' => $danada,
                'motivo_dano' => $danada > 0 ? ($linea['motivo_dano'] ?? null) : null,
            ];
            $totalRecibida += $recibida;

            if ($danada > 0) {
                $producto = DB::table('productos')->where('id', $productoId)->first(['nombre']);
                $danosResumen[] = "{$danada} und. de ".($producto->nombre ?? "producto #{$productoId}");
            }
        }

        if ($totalRecibida <= 0 || empty($lineasValidas)) {
            throw ValidationException::withMessages([
                'lineas' => 'Debes registrar al menos una cantidad recibida mayor a cero.',
            ]);
        }

        DB::transaction(function () use ($request, $row, $data, $lineasValidas, $danosResumen) {
            $descargaId = DB::table('descargas')->insertGetId([
                'recepcion_id' => $row->id,
                'bodeguero_id' => $request->user()->id,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            foreach ($lineasValidas as $linea) {
                DB::table('detalle_descarga')->insert([
                    'descarga_id' => $descargaId,
                    'producto_id' => $linea['producto_id'],
                    'cantidad_recibida' => $linea['cantidad_recibida'],
                    'cantidad_danada' => $linea['cantidad_danada'],
                    'motivo_dano' => $linea['motivo_dano'],
                ]);
            }

            DB::table('recepciones')->where('id', $row->id)->update([
                'estado' => 'descargada',
            ]);

            if (! empty($danosResumen)) {
                DB::table('notificaciones')->insert([
                    'destinatario_tipo' => 'proveedor',
                    'destinatario_id' => $row->proveedor_id,
                    'canal' => 'portal',
                    'tipo_evento' => 'danos_descarga',
                    'titulo' => 'Daños detectados en su entrega',
                    'mensaje' => 'Se registraron daños en la recepción '.$row->codigo_recepcion.': '.implode('; ', $danosResumen).'.',
                    'referencia_tipo' => 'recepcion',
                    'referencia_id' => $row->id,
                ]);
            }
        });

        return redirect()
            ->route('bodeguero.pendientes.index')
            ->with('status', 'Descarga confirmada. Inventario y pérdidas actualizados.');
    }

    private function findPendiente(int $id): object
    {
        $row = DB::table('recepciones as r')
            ->join('vehiculos as v', 'v.id', '=', 'r.vehiculo_id')
            ->join('proveedores as p', 'p.id', '=', 'r.proveedor_id')
            ->leftJoin('ordenes_entrega as o', 'o.id', '=', 'r.orden_entrega_id')
            ->where('r.id', $id)
            ->first([
                'r.*',
                'v.placa',
                'v.conductor',
                'p.nombre as proveedor',
                'o.codigo_orden',
            ]);

        abort_unless($row, 404);

        if ($row->estado !== 'pendiente') {
            abort(422, 'Solo se pueden descargar recepciones en estado pendiente.');
        }

        $yaDescargada = DB::table('descargas')->where('recepcion_id', $id)->exists();
        if ($yaDescargada) {
            abort(422, 'Esta recepción ya tiene una descarga confirmada.');
        }

        return $row;
    }
}
