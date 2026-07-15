<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Índices en ventas para acelerar búsquedas y joins
        Schema::table('ventas', function (Blueprint $table) {
            if (!$this->indexExists('ventas', 'ventas_id_cliente_index')) {
                $table->index('id_cliente', 'ventas_id_cliente_index');
            }
            if (!$this->indexExists('ventas', 'ventas_id_tipo_comprobante_index')) {
                $table->index('id_tipo_comprobante', 'ventas_id_tipo_comprobante_index');
            }
            if (!$this->indexExists('ventas', 'ventas_fecha_venta_index')) {
                $table->index('fecha_venta', 'ventas_fecha_venta_index');
            }
        });

        // Índices en detalle_venta
        Schema::table('detalle_venta', function (Blueprint $table) {
            if (!$this->indexExists('detalle_venta', 'detalle_venta_id_venta_index')) {
                $table->index('id_venta', 'detalle_venta_id_venta_index');
            }
            if (!$this->indexExists('detalle_venta', 'detalle_venta_id_producto_index')) {
                $table->index('id_producto', 'detalle_venta_id_producto_index');
            }
        });

        // Índices en movimientos_inventario
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            if (!$this->indexExists('movimientos_inventario', 'mov_inv_id_producto_index')) {
                $table->index('id_producto', 'mov_inv_id_producto_index');
            }
            if (!$this->indexExists('movimientos_inventario', 'mov_inv_fecha_index')) {
                $table->index('fecha_movimiento', 'mov_inv_fecha_index');
            }
        });

        // Índice en productos para búsquedas por categoría y estado
        Schema::table('productos', function (Blueprint $table) {
            if (!$this->indexExists('productos', 'productos_id_categoria_index')) {
                $table->index('id_categoria', 'productos_id_categoria_index');
            }
            if (!$this->indexExists('productos', 'productos_estado_index')) {
                $table->index('estado', 'productos_estado_index');
            }
        });

        // Índice en clientes para búsquedas rápidas
        Schema::table('clientes', function (Blueprint $table) {
            if (!$this->indexExists('clientes', 'clientes_nombres_index')) {
                $table->index('nombres', 'clientes_nombres_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex('ventas_id_cliente_index');
            $table->dropIndex('ventas_id_tipo_comprobante_index');
            $table->dropIndex('ventas_fecha_venta_index');
        });
        Schema::table('detalle_venta', function (Blueprint $table) {
            $table->dropIndex('detalle_venta_id_venta_index');
            $table->dropIndex('detalle_venta_id_producto_index');
        });
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropIndex('mov_inv_id_producto_index');
            $table->dropIndex('mov_inv_fecha_index');
        });
        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex('productos_id_categoria_index');
            $table->dropIndex('productos_estado_index');
        });
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex('clientes_nombres_index');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return collect(\DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$index}'"))->isNotEmpty();
    }
};
