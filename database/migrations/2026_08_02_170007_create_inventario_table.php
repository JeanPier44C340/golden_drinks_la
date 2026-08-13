<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id')->unique();
            $table->integer('stock_actual')->default(0);
            $table->timestamp('ultima_actualizacion')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('producto_id', 'fk_inventario_producto')
                ->references('id')->on('productos')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->comment('Existencias actuales por producto (espejo en tiempo real)');
        });

        DB::statement("ALTER TABLE `inventario` ADD CONSTRAINT `inventario_stock_actual_check` CHECK (stock_actual >= 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario');
    }
};
