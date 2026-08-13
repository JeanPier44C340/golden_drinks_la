<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_orden_entrega', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_entrega_id');
            $table->unsignedBigInteger('producto_id');
            $table->integer('cantidad_programada');
            $table->unique(['orden_entrega_id', 'producto_id'], 'uq_orden_producto');

            $table->foreign('orden_entrega_id', 'fk_detalle_orden_orden')
                ->references('id')->on('ordenes_entrega')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('producto_id', 'fk_detalle_orden_producto')
                ->references('id')->on('productos')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->comment('Items programados de cada orden de entrega');
        });

        DB::statement("ALTER TABLE `detalle_orden_entrega` ADD CONSTRAINT `detalle_orden_entrega_cantidad_programada_check` CHECK (cantidad_programada > 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_orden_entrega');
    }
};
