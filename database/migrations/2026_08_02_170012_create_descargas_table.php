<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('descargas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recepcion_id')->unique();
            $table->unsignedBigInteger('bodeguero_id');
            $table->dateTime('confirmada_en')->useCurrent();
            $table->text('observaciones')->nullable();

            $table->foreign('recepcion_id', 'fk_descarga_recepcion')
                ->references('id')->on('recepciones')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('bodeguero_id', 'fk_descarga_bodeguero')
                ->references('id')->on('usuarios')
                ->onUpdate('cascade')->onDelete('restrict');

            $table->comment('Confirmacion de descarga por recepcion (HU-BOD-003)');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('descargas');
    }
};
