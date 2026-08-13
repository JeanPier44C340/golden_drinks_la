<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perdidas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recepcion_id')->nullable();
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('bodeguero_id')->nullable();
            $table->enum('origen', ['descarga', 'bodega'])->default('descarga');
            $table->integer('cantidad');
            $table->string('motivo', 180);
            $table->string('evidencia_url', 255)->nullable();
            $table->dateTime('registrada_en')->useCurrent();

            $table->foreign('recepcion_id', 'fk_perdida_recepcion')
                ->references('id')->on('recepciones')
                ->onUpdate('cascade')->onDelete('set null');
            $table->foreign('producto_id', 'fk_perdida_producto')
                ->references('id')->on('productos')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('bodeguero_id', 'fk_perdida_bodeguero')
                ->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('set null');

            $table->comment('Registro trazable de productos danados (descarga o bodega)');
        });

        DB::statement("ALTER TABLE `perdidas` ADD CONSTRAINT `perdidas_cantidad_check` CHECK (cantidad > 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('perdidas');
    }
};
