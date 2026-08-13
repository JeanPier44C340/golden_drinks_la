<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_reclamo_archivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reclamo_id');
            $table->string('archivo_url', 255);
            $table->string('tipo_archivo', 50);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('reclamo_id', 'fk_reclamo_archivo')
                ->references('id')->on('reclamos')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->comment('Evidencias adjuntas a un reclamo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_reclamo_archivos');
    }
};
