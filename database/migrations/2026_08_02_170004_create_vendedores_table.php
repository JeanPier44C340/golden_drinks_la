<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendedores', function (Blueprint $table) {
            $table->id();
            $table->string('empresa', 150);
            $table->string('nombre_contacto', 120);
            $table->string('correo', 150)->unique();
            $table->string('password_hash', 255);
            $table->string('telefono', 30)->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->comment('Vendedores externos que realizan pedidos comerciales');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendedores');
    }
};
