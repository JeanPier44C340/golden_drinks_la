<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesiones_auditoria', function (Blueprint $table) {
            $table->id();
            $table->enum('usuario_tipo', ['usuario', 'proveedor', 'vendedor']);
            $table->integer('usuario_id');
            $table->enum('accion', ['login', 'logout'])->default('login');
            $table->string('ip_origen', 45);
            $table->string('navegador', 180)->nullable();
            $table->enum('resultado', ['exitoso', 'fallido']);
            $table->timestamp('created_at')->useCurrent();
            $table->comment('Trazabilidad de inicios y cierres de sesion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones_auditoria');
    }
};
