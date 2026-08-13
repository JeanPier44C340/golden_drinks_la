<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_descarga', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('descarga_id');
            $table->unsignedBigInteger('producto_id');
            $table->integer('cantidad_recibida')->default(0);
            $table->integer('cantidad_danada')->default(0);
            $table->string('motivo_dano', 180)->nullable();
            $table->unique(['descarga_id', 'producto_id'], 'uq_descarga_producto');

            $table->foreign('descarga_id', 'fk_detalle_descarga_descarga')
                ->references('id')->on('descargas')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('producto_id', 'fk_detalle_descarga_producto')
                ->references('id')->on('productos')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->comment('Cantidades buenas/danadas por producto en la descarga');
        });

        DB::statement("ALTER TABLE `detalle_descarga` ADD CONSTRAINT `detalle_descarga_cantidad_recibida_check` CHECK (cantidad_recibida >= 0)");
        DB::statement("ALTER TABLE `detalle_descarga` ADD CONSTRAINT `detalle_descarga_cantidad_danada_check` CHECK (cantidad_danada >= 0)");
        DB::statement("ALTER TABLE `detalle_descarga` ADD CONSTRAINT `chk_danado_no_supera` CHECK (cantidad_danada <= cantidad_recibida)");
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_descarga');
    }
};
