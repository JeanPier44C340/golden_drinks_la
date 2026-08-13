<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 40)->unique();
            $table->string('nombre', 150);
            $table->unsignedBigInteger('categoria_id');
            $table->string('unidad_medida', 20)->default('botella');
            $table->decimal('precio_compra', 12, 2)->default(0);
            $table->decimal('precio_distribucion', 12, 2)->default(0);
            $table->integer('stock_minimo')->default(10);
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('categoria_id', 'fk_productos_categoria')
                ->references('id')->on('categorias_producto')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->comment('Catalogo maestro de productos');
        });

        DB::statement("ALTER TABLE `productos` ADD CONSTRAINT `productos_precio_compra_check` CHECK (precio_compra >= 0)");
        DB::statement("ALTER TABLE `productos` ADD CONSTRAINT `productos_precio_distribucion_check` CHECK (precio_distribucion >= 0)");
        DB::statement("ALTER TABLE `productos` ADD CONSTRAINT `productos_stock_minimo_check` CHECK (stock_minimo >= 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
