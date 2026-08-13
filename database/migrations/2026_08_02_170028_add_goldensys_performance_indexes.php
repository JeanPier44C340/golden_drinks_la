<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->index('estado', 'idx_usuario_estado');
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->index('categoria_id', 'idx_producto_categoria');
            $table->index('activo', 'idx_producto_activo');
        });

        Schema::table('vehiculos', function (Blueprint $table) {
            $table->index('estado', 'idx_vehiculo_estado');
            $table->index('registrado_por_admin_id', 'idx_vehiculo_admin');
        });

        Schema::table('recepciones', function (Blueprint $table) {
            $table->index('hora_llegada', 'idx_recepcion_fecha');
            $table->index('hora_salida', 'idx_recepcion_salida');
            $table->index('estado', 'idx_recepcion_estado');
            $table->index('proveedor_id', 'idx_recepcion_proveedor');
        });

        Schema::table('ordenes_entrega', function (Blueprint $table) {
            $table->index(['proveedor_id', 'fecha_estimada'], 'idx_orden_proveedor_fecha');
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->index(['producto_id', 'created_at'], 'idx_movimiento_producto_fecha');
            $table->index(['origen_tipo', 'origen_id'], 'idx_movimiento_origen');
        });

        Schema::table('alertas_stock', function (Blueprint $table) {
            $table->index('estado', 'idx_alerta_estado');
            $table->index(['producto_id', 'estado'], 'idx_alerta_producto');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->index('estado', 'idx_pedido_estado');
            $table->index('pago_estado', 'idx_pedido_pago');
            $table->index('vendedor_id', 'idx_pedido_vendedor');
        });

        Schema::table('despachos', function (Blueprint $table) {
            $table->index('estado', 'idx_despacho_estado');
            $table->index('repartidor_id', 'idx_despacho_repartidor');
        });

        Schema::table('reclamos', function (Blueprint $table) {
            $table->index('estado', 'idx_reclamo_estado');
        });

        Schema::table('perdidas', function (Blueprint $table) {
            $table->index(['producto_id', 'registrada_en'], 'idx_perdida_producto');
        });

        Schema::table('notificaciones', function (Blueprint $table) {
            $table->index(['destinatario_tipo', 'destinatario_id', 'leida'], 'idx_notif_destinatario');
        });

        Schema::table('sesiones_auditoria', function (Blueprint $table) {
            $table->index(['usuario_tipo', 'usuario_id', 'created_at'], 'idx_sesion_usuario');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropIndex('idx_usuario_estado');
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex('idx_producto_categoria');
            $table->dropIndex('idx_producto_activo');
        });

        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropIndex('idx_vehiculo_estado');
            $table->dropIndex('idx_vehiculo_admin');
        });

        Schema::table('recepciones', function (Blueprint $table) {
            $table->dropIndex('idx_recepcion_fecha');
            $table->dropIndex('idx_recepcion_salida');
            $table->dropIndex('idx_recepcion_estado');
            $table->dropIndex('idx_recepcion_proveedor');
        });

        Schema::table('ordenes_entrega', function (Blueprint $table) {
            $table->dropIndex('idx_orden_proveedor_fecha');
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropIndex('idx_movimiento_producto_fecha');
            $table->dropIndex('idx_movimiento_origen');
        });

        Schema::table('alertas_stock', function (Blueprint $table) {
            $table->dropIndex('idx_alerta_estado');
            $table->dropIndex('idx_alerta_producto');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndex('idx_pedido_estado');
            $table->dropIndex('idx_pedido_pago');
            $table->dropIndex('idx_pedido_vendedor');
        });

        Schema::table('despachos', function (Blueprint $table) {
            $table->dropIndex('idx_despacho_estado');
            $table->dropIndex('idx_despacho_repartidor');
        });

        Schema::table('reclamos', function (Blueprint $table) {
            $table->dropIndex('idx_reclamo_estado');
        });

        Schema::table('perdidas', function (Blueprint $table) {
            $table->dropIndex('idx_perdida_producto');
        });

        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropIndex('idx_notif_destinatario');
        });

        Schema::table('sesiones_auditoria', function (Blueprint $table) {
            $table->dropIndex('idx_sesion_usuario');
        });
    }
};
