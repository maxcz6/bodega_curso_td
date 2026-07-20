<?php

namespace App\Models;

class DetalleVenta
{
    public bool $timestamps = false;

    protected string $table      = 'detalle_venta';
    protected string $primaryKey = 'id_detalle';
    protected array  $fillable   = [
        'id_venta', 'id_producto',
        'cantidad', 'precio_unitario', 'subtotal',
    ];

    public function getTable(): string   { return $this->table; }
    public function getKeyName(): string  { return $this->primaryKey; }
    public function getFillable(): array  { return $this->fillable; }
    public function getHidden(): array    { return []; }
}
