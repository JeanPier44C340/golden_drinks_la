<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despachos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id')->nullable();
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('repartidor_id');
            $table->string('codigo_despacho', 40)->unique();
            $table->enum('estado', ['creado', 'en_camino', 'entregado', 'cancelado'])->default('creado');
            $table->dateTime('despachado_en')->useCurrent();
            $table->dateTime('entregado_en')->nullable();
            $table->string('motivo_cancelacion', 180)->nullable();
            $table->text('observaciones')->nullable();

            $table->foreign('pedido_id', 'fk_despacho_pedido')
                ->references('id')->on('pedidos')
                ->onUpdate('cascade')->onDelete('set null');
            $table->foreign('admin_id', 'fk_despacho_admin')
                ->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('repartidor_id', 'fk_despacho_repartidor')
                ->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->comment('Salida de mercancia asignada a un repartidor (HU-ADM-028)');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despachos');
    }
};
