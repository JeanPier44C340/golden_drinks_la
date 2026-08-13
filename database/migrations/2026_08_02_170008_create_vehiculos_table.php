<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('placa', 20)->unique();
            $table->string('conductor', 120);
            $table->string('tipo_vehiculo', 50)->nullable();
            $table->integer('capacidad_cajas')->nullable();
            $table->enum('estado', ['disponible', 'en_mantenimiento', 'inactivo'])->default('disponible');
            $table->string('observaciones', 180)->nullable();
            $table->unsignedBigInteger('registrado_por_admin_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('registrado_por_admin_id', 'fk_vehiculos_admin')
                ->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('set null');

            $table->comment('Vehiculos que ingresan a la bodega');
        });

        DB::statement("ALTER TABLE `vehiculos` ADD CONSTRAINT `vehiculos_capacidad_cajas_check` CHECK (capacidad_cajas IS NULL OR capacidad_cajas >= 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
