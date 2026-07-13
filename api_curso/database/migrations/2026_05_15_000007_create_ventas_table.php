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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id('id_venta');
            $table->string('numero_comprobante', 30)->unique();
            $table->timestamp('fecha_venta')->useCurrent();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('igv', 10, 2);
            $table->decimal('total', 10, 2);
            $table->unsignedBigInteger('id_cliente');
            $table->integer('id_tipo_comprobante');
            $table->unsignedBigInteger('id_usuario');

            $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
            $table->foreign('id_tipo_comprobante')->references('id_tipo_comprobante')->on('tipo_comprobante');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
