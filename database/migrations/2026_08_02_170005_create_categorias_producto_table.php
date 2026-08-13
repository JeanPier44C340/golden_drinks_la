<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_producto', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80)->unique();
            $table->string('descripcion', 180)->nullable();
            $table->comment('Categorias de licores: vino, ron, aguardiente, etc.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_producto');
    }
};
