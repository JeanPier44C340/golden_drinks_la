<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_pago_archivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->string('archivo_url', 255);
            $table->string('tipo_archivo', 50)->default('comprobante_pago');
            $table->decimal('monto', 12, 2)->nullable();
            $table->string('referencia', 120)->nullable();
            $table->timestamp('subido_en')->useCurrent();

            $table->foreign('pedido_id', 'fk_pago_pedido')
                ->references('id')->on('pedidos')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->comment('Evidencias de pago adjuntadas por el vendedor (HU-VEN-031)');
        });

        DB::statement("ALTER TABLE `pedido_pago_archivos` ADD CONSTRAINT `pedido_pago_archivos_monto_check` CHECK (monto IS NULL OR monto >= 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_pago_archivos');
    }
};
