<?php

namespace App\Models;

class Venta
{
    public bool $timestamps = false;

    protected string $table      = 'ventas';
    protected string $primaryKey = 'id_venta';
    protected array  $fillable   = [
        'numero_comprobante', 'fecha_venta',
        'subtotal', 'igv', 'total',
        'id_cliente', 'id_tipo_comprobante',
    ];

    public function getTable(): string   { return $this->table; }
    public function getKeyName(): string  { return $this->primaryKey; }
    public function getFillable(): array  { return $this->fillable; }
    public function getHidden(): array    { return []; }
}
