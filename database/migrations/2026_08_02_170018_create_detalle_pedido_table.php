<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_pedido', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('producto_id');
            $table->integer('cantidad_solicitada');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->unique(['pedido_id', 'producto_id'], 'uq_pedido_producto');

            $table->foreign('pedido_id', 'fk_detalle_pedido_pedido')
                ->references('id')->on('pedidos')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('producto_id', 'fk_detalle_pedido_producto')
                ->references('id')->on('productos')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->comment('Items de cada pedido comercial');
        });

        DB::statement("ALTER TABLE `detalle_pedido` ADD CONSTRAINT `detalle_pedido_cantidad_solicitada_check` CHECK (cantidad_solicitada > 0)");
        DB::statement("ALTER TABLE `detalle_pedido` ADD CONSTRAINT `detalle_pedido_precio_unitario_check` CHECK (precio_unitario >= 0)");
        DB::statement("ALTER TABLE `detalle_pedido` ADD CONSTRAINT `detalle_pedido_subtotal_check` CHECK (subtotal >= 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_pedido');
    }
};
