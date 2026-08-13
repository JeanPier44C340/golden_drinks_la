<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AlertarOrdenesDia extends Command
{
    protected $signature = 'goldensys:alertar-ordenes-dia';

    protected $description = 'Notifica a celadores las órdenes de entrega programadas para hoy (RF-16)';

    public function handle(): int
    {
        $hoy = today()->toDateString();

        $ordenes = DB::table('ordenes_entrega as o')
            ->join('proveedores as p', 'p.id', '=', 'o.proveedor_id')
            ->where('o.estado', 'programada')
            ->whereDate('o.fecha_estimada', $hoy)
            ->get([
                'o.id',
                'o.codigo_orden',
                'o.proveedor_id',
                'p.nombre as proveedor',
            ]);

        if ($ordenes->isEmpty()) {
            $this->info('Sin órdenes programadas para hoy.');

            return self::SUCCESS;
        }

        $celadores = DB::table('usuarios as u')
            ->join('roles as r', 'r.id', '=', 'u.rol_id')
            ->where('r.nombre', 'celador')
            ->where('u.estado', 'activo')
            ->pluck('u.id');

        if ($celadores->isEmpty()) {
            $this->warn('No hay celadores activos para notificar.');

            return self::SUCCESS;
        }

        $creadas = 0;

        foreach ($ordenes as $orden) {
            $detalle = DB::table('detalle_orden_entrega as doe')
                ->join('productos as pr', 'pr.id', '=', 'doe.producto_id')
                ->where('doe.orden_entrega_id', $orden->id)
                ->get(['pr.nombre', 'doe.cantidad_programada']);

            $resumen = $detalle->map(fn ($d) => $d->cantidad_programada.' × '.$d->nombre)->implode('; ');

            foreach ($celadores as $celadorId) {
                $yaExiste = DB::table('notificaciones')
                    ->where('destinatario_tipo', 'usuario')
                    ->where('destinatario_id', $celadorId)
                    ->where('tipo_evento', 'orden_entrega_dia')
                    ->where('referencia_tipo', 'orden_entrega')
                    ->where('referencia_id', $orden->id)
                    ->whereDate('created_at', $hoy)
                    ->exists();

                if ($yaExiste) {
                    continue;
                }

                DB::table('notificaciones')->insert([
                    'destinatario_tipo' => 'usuario',
                    'destinatario_id' => $celadorId,
                    'canal' => 'portal',
                    'tipo_evento' => 'orden_entrega_dia',
                    'titulo' => 'Orden de entrega para hoy',
                    'mensaje' => 'Proveedor '.$orden->proveedor.' · '.$orden->codigo_orden.': '.$resumen,
                    'referencia_tipo' => 'orden_entrega',
                    'referencia_id' => $orden->id,
                ]);
                $creadas++;
            }
        }

        $this->info("Notificaciones creadas: {$creadas}");

        return self::SUCCESS;
    }
}
