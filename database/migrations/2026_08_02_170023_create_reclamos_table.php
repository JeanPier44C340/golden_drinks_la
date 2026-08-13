<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reclamos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('vendedor_id');
            $table->text('descripcion');
            $table->integer('cantidad_afectada')->default(0);
            $table->enum('estado', ['abierto', 'en_revision', 'resuelto'])->default('abierto');
            $table->text('respuesta_admin')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('pedido_id', 'fk_reclamo_pedido')
                ->references('id')->on('pedidos')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('vendedor_id', 'fk_reclamo_vendedor')
                ->references('id')->on('vendedores')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->comment('Reclamos del vendedor sobre pedidos despachados (HU-VEN-022)');
        });

        DB::statement("ALTER TABLE `reclamos` ADD CONSTRAINT `reclamos_cantidad_afectada_check` CHECK (cantidad_afectada >= 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('reclamos');
    }
};
