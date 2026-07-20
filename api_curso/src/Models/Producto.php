<?php

namespace App\Models;

/**
 * Stub del modelo Producto para pruebas sin Laravel.
 * Replica las propiedades que verifican los tests.
 */
class Producto
{
    public bool $timestamps = false;

    protected string $table      = 'productos';
    protected string $primaryKey = 'id_producto';
    protected array  $fillable   = [
        'nombre', 'descripcion', 'codigo_barras',
        'precio_compra', 'precio_venta',
        'stock_actual', 'stock_minimo',
        'estado', 'id_categoria',
    ];

    public function getTable(): string   { return $this->table; }
    public function getKeyName(): string  { return $this->primaryKey; }
    public function getFillable(): array  { return $this->fillable; }
    public function getHidden(): array    { return []; }
}
