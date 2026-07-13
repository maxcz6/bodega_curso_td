<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id('id_movimiento');
            $table->unsignedBigInteger('id_producto');
            $table->integer('id_motivo');
            $table->unsignedBigInteger('id_usuario');
            $table->string('tipo_movimiento', 10); // ENTRADA or SALIDA
            $table->integer('cantidad');
            $table->integer('stock_anterior');
            $table->integer('stock_nuevo');
            $table->timestamp('fecha_movimiento')->useCurrent();
            $table->text('observaciones')->nullable();

            $table->foreign('id_producto')->references('id_producto')->on('productos');
            $table->foreign('id_motivo')->references('id_motivo')->on('motivos_movimiento');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
