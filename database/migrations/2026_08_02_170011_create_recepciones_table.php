<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recepciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_entrega_id')->nullable();
            $table->unsignedBigInteger('vehiculo_id');
            $table->unsignedBigInteger('proveedor_id');
            $table->unsignedBigInteger('celador_id');
            $table->string('codigo_recepcion', 40)->unique();
            $table->dateTime('hora_llegada')->useCurrent();
            $table->dateTime('hora_salida')->nullable();
            $table->unsignedBigInteger('celador_salida_id')->nullable();
            $table->string('salida_observaciones', 180)->nullable();
            $table->decimal('valor_flete', 12, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['pendiente', 'descargada', 'salida', 'cancelada'])->default('pendiente');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('orden_entrega_id', 'fk_recepcion_orden')
                ->references('id')->on('ordenes_entrega')
                ->onUpdate('cascade')->onDelete('set null');
            $table->foreign('vehiculo_id', 'fk_recepcion_vehiculo')
                ->references('id')->on('vehiculos')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('proveedor_id', 'fk_recepcion_proveedor')
                ->references('id')->on('proveedores')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('celador_id', 'fk_recepcion_celador')
                ->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('celador_salida_id', 'fk_recepcion_celador_salida')
                ->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('set null');

            $table->comment('Recepcion con ciclo completo llegada->descarga->salida');
        });

        DB::statement("ALTER TABLE `recepciones` ADD CONSTRAINT `recepciones_valor_flete_check` CHECK (valor_flete >= 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('recepciones');
    }
};
