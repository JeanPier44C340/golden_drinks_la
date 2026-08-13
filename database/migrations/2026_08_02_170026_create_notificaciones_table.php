<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->enum('destinatario_tipo', ['usuario', 'proveedor', 'vendedor']);
            $table->integer('destinatario_id');
            $table->enum('canal', ['portal', 'correo'])->default('portal');
            $table->string('tipo_evento', 60);
            $table->string('titulo', 150);
            $table->text('mensaje');
            $table->string('referencia_tipo', 40)->nullable();
            $table->integer('referencia_id')->nullable();
            $table->boolean('leida')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->comment('Bandeja de notificaciones por actor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
