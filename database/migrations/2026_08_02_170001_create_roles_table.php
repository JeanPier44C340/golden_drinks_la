<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 180);
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->comment('Roles operativos internos: administrador, celador, bodeguero, repartidor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
