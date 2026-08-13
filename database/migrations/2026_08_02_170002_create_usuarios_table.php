<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo', 120);
            $table->string('correo', 150)->unique();
            $table->string('password_hash', 255);
            $table->string('telefono', 30)->nullable();
            $table->unsignedBigInteger('rol_id');
            $table->enum('estado', ['activo', 'inactivo', 'bloqueado'])->default('activo');
            $table->dateTime('ultimo_acceso')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('rol_id', 'fk_usuarios_roles')
                ->references('id')->on('roles')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->comment('Cuentas internas del sistema (HU-SEC-024, HU-ADM-011)');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
