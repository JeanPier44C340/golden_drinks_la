<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->enum('tipo_movimiento', ['entrada', 'salida', 'perdida', 'ajuste']);
            $table->enum('origen_tipo', ['descarga', 'despacho', 'perdida', 'entrega', 'manual']);
            $table->integer('origen_id');
            $table->integer('cantidad');
            $table->integer('saldo_anterior');
            $table->integer('saldo_resultante');
            $table->unsignedBigInteger('actor_usuario_id')->nullable();
            $table->string('nota', 180)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('producto_id', 'fk_mov_inventario_producto')
                ->references('id')->on('productos')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('actor_usuario_id', 'fk_mov_inventario_actor')
                ->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('set null');

            $table->comment('Kardex inmutable: cada entrada/salida/perdida con saldo');
        });

        DB::statement("ALTER TABLE `movimientos_inventario` ADD CONSTRAINT `movimientos_inventario_cantidad_check` CHECK (cantidad <> 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
