<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GoldenSysDemoFlowSeeder extends Seeder
{
    /**
     * Flujo de demostración de BD/GoldenDrinks_Base_Datos.sql (sección 11).
     * Requiere triggers activos (migración 170029).
     */
    public function run(): void
    {
        DB::table('ordenes_entrega')->insert([
            'id' => 1,
            'proveedor_id' => 1,
            'codigo_orden' => 'OE-0001',
            'fecha_estimada' => DB::raw('NOW() + INTERVAL 1 DAY'),
            'estado' => 'programada',
        ]);

        DB::table('detalle_orden_entrega')->insert([
            ['orden_entrega_id' => 1, 'producto_id' => 1, 'cantidad_programada' => 50],
            ['orden_entrega_id' => 1, 'producto_id' => 2, 'cantidad_programada' => 30],
        ]);

        DB::table('recepciones')->insert([
            'id' => 1,
            'orden_entrega_id' => 1,
            'vehiculo_id' => 1,
            'proveedor_id' => 1,
            'celador_id' => 2,
            'codigo_recepcion' => 'REC-0001',
            'valor_flete' => 80000,
            'estado' => 'pendiente',
        ]);

        DB::table('descargas')->insert([
            'id' => 1,
            'recepcion_id' => 1,
            'bodeguero_id' => 3,
        ]);

        DB::table('detalle_descarga')->insert([
            ['descarga_id' => 1, 'producto_id' => 1, 'cantidad_recibida' => 50, 'cantidad_danada' => 2, 'motivo_dano' => 'Botellas rotas en transporte'],
            ['descarga_id' => 1, 'producto_id' => 2, 'cantidad_recibida' => 30, 'cantidad_danada' => 0, 'motivo_dano' => null],
        ]);

        DB::table('recepciones')->where('id', 1)->update(['estado' => 'descargada']);

        DB::table('recepciones')->where('id', 1)->update([
            'hora_salida' => DB::raw('NOW()'),
            'celador_salida_id' => 2,
            'salida_observaciones' => 'Sale tras descargar',
        ]);

        DB::table('pedidos')->insert([
            'id' => 1,
            'vendedor_id' => 1,
            'codigo_pedido' => 'PED-0001',
            'estado' => 'en_revision',
            'pago_estado' => 'pendiente',
        ]);

        DB::table('detalle_pedido')->insert([
            ['pedido_id' => 1, 'producto_id' => 1, 'cantidad_solicitada' => 20, 'precio_unitario' => 26000, 'subtotal' => 0],
            ['pedido_id' => 1, 'producto_id' => 2, 'cantidad_solicitada' => 10, 'precio_unitario' => 45000, 'subtotal' => 0],
        ]);

        DB::table('pedido_pago_archivos')->insert([
            'pedido_id' => 1,
            'archivo_url' => '/uploads/pagos/ped-0001.jpg',
            'tipo_archivo' => 'comprobante_pago',
            'monto' => 970000,
            'referencia' => 'TRX-558211',
        ]);

        DB::table('pedidos')->where('id', 1)->update([
            'pago_estado' => 'verificado',
            'pago_verificado_por' => 1,
        ]);

        DB::table('pedidos')->where('id', 1)->update([
            'estado' => 'aprobado',
            'admin_aprobador_id' => 1,
        ]);

        DB::table('despachos')->insert([
            'id' => 1,
            'pedido_id' => 1,
            'admin_id' => 1,
            'repartidor_id' => 4,
            'codigo_despacho' => 'DESP-0001',
            'estado' => 'creado',
        ]);

        DB::table('detalle_despacho')->insert([
            ['despacho_id' => 1, 'producto_id' => 1, 'cantidad' => 20],
            ['despacho_id' => 1, 'producto_id' => 2, 'cantidad' => 10],
        ]);

        DB::table('pedidos')->where('id', 1)->update(['estado' => 'despachado']);
        DB::table('despachos')->where('id', 1)->update(['estado' => 'en_camino']);

        DB::table('despacho_entrega_archivos')->insert([
            'despacho_id' => 1,
            'repartidor_id' => 4,
            'archivo_url' => '/uploads/entregas/desp-0001.jpg',
            'tipo_archivo' => 'foto_entrega',
            'latitud' => 2.9273,
            'longitud' => -75.2819,
        ]);

        DB::table('notificaciones')->insert([
            'destinatario_tipo' => 'proveedor',
            'destinatario_id' => 1,
            'canal' => 'portal',
            'tipo_evento' => 'danos_descarga',
            'titulo' => 'Daños detectados en su entrega',
            'mensaje' => 'Se registraron 2 unidades dañadas de Vino Tinto Reserva en la recepción REC-0001.',
            'referencia_tipo' => 'recepcion',
            'referencia_id' => 1,
        ]);

        DB::statement('CALL sp_sincronizar_alertas_stock()');
    }
}
