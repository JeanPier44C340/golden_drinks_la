<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas_stock', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->integer('stock_detectado');
            $table->integer('stock_minimo');
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->dateTime('abierta_en')->useCurrent();
            $table->dateTime('cerrada_en')->nullable();

            $table->foreign('producto_id', 'fk_alerta_producto')
                ->references('id')->on('productos')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->comment('Alertas generadas/cerradas automaticamente por triggers');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas_stock');
    }
};
