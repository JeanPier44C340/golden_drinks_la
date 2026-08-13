<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendedor_id');
            $table->string('codigo_pedido', 40)->unique();
            $table->dateTime('fecha_pedido')->useCurrent();
            $table->enum('estado', ['en_revision', 'aprobado', 'rechazado', 'despachado', 'entregado'])->default('en_revision');
            $table->enum('pago_estado', ['pendiente', 'verificado', 'rechazado'])->default('pendiente');
            $table->unsignedBigInteger('pago_verificado_por')->nullable();
            $table->dateTime('pago_verificado_en')->nullable();
            $table->string('motivo_rechazo', 255)->nullable();
            $table->unsignedBigInteger('admin_aprobador_id')->nullable();
            $table->unsignedBigInteger('repartidor_id')->nullable();
            $table->decimal('valor_total', 12, 2)->default(0);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('vendedor_id', 'fk_pedido_vendedor')
                ->references('id')->on('vendedores')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('admin_aprobador_id', 'fk_pedido_admin')
                ->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('set null');
            $table->foreign('pago_verificado_por', 'fk_pedido_pago_verif')
                ->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('set null');
            $table->foreign('repartidor_id', 'fk_pedido_repartidor')
                ->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('set null');

            $table->comment('Pedidos comerciales con control de pago (RF-19, RF-24)');
        });

        DB::statement("ALTER TABLE `pedidos` ADD CONSTRAINT `pedidos_valor_total_check` CHECK (valor_total >= 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
