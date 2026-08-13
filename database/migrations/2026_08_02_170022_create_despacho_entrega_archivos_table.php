<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despacho_entrega_archivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('despacho_id');
            $table->unsignedBigInteger('repartidor_id');
            $table->string('archivo_url', 255);
            $table->string('tipo_archivo', 50)->default('foto_entrega');
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->timestamp('entregado_en')->useCurrent();

            $table->foreign('despacho_id', 'fk_entrega_despacho')
                ->references('id')->on('despachos')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('repartidor_id', 'fk_entrega_repartidor')
                ->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->comment('Foto que confirma la entrega del pedido (HU-DES-032)');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despacho_entrega_archivos');
    }
};
