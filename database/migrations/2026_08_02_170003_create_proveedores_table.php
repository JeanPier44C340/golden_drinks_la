<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nit', 30)->unique();
            $table->string('nombre', 150);
            $table->string('correo', 150)->unique();
            $table->string('password_hash', 255);
            $table->string('telefono', 30)->nullable();
            $table->string('direccion', 180)->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->comment('Proveedores que entregan mercancia a la bodega');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
