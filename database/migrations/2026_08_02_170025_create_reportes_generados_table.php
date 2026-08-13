<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_generados', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_reporte', ['informe_mensual', 'facturacion_proveedor', 'historial_vendedor', 'inventario']);
            $table->date('periodo_desde');
            $table->date('periodo_hasta');
            $table->unsignedBigInteger('usuario_generador_id')->nullable();
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->unsignedBigInteger('vendedor_id')->nullable();
            $table->string('ruta_archivo', 255);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('usuario_generador_id', 'fk_reporte_usuario')
                ->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('set null');
            $table->foreign('proveedor_id', 'fk_reporte_proveedor')
                ->references('id')->on('proveedores')
                ->onUpdate('cascade')->onDelete('set null');
            $table->foreign('vendedor_id', 'fk_reporte_vendedor')
                ->references('id')->on('vendedores')
                ->onUpdate('cascade')->onDelete('set null');

            $table->comment('Reportes PDF almacenados para descarga (RN-13)');
        });

        DB::statement("ALTER TABLE `reportes_generados` ADD CONSTRAINT `chk_periodo` CHECK (periodo_hasta >= periodo_desde)");
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_generados');
    }
};
