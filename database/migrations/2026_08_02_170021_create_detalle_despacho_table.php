<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_despacho', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('despacho_id');
            $table->unsignedBigInteger('producto_id');
            $table->integer('cantidad');
            $table->unique(['despacho_id', 'producto_id'], 'uq_despacho_producto');

            $table->foreign('despacho_id', 'fk_detalle_despacho_despacho')
                ->references('id')->on('despachos')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('producto_id', 'fk_detalle_despacho_producto')
                ->references('id')->on('productos')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->comment('Items despachados; descuentan inventario via trigger');
        });

        DB::statement("ALTER TABLE `detalle_despacho` ADD CONSTRAINT `detalle_despacho_cantidad_check` CHECK (cantidad > 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_despacho');
    }
};
