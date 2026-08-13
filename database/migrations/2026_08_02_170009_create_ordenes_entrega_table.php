<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_entrega', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proveedor_id');
            $table->string('codigo_orden', 40)->unique();
            $table->dateTime('fecha_estimada');
            $table->enum('estado', ['programada', 'en_proceso', 'recibida', 'cancelada'])->default('programada');
            $table->text('observaciones')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('proveedor_id', 'fk_ordenes_entrega_proveedor')
                ->references('id')->on('proveedores')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->comment('Programacion anticipada de entregas (RN-10: 24h)');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_entrega');
    }
};
