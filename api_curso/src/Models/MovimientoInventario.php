<?php

namespace App\Models;

class MovimientoInventario
{
    public bool $timestamps = false;

    protected string $table      = 'movimientos_inventario';
    protected string $primaryKey = 'id_movimiento';
    protected array  $fillable   = [
        'id_producto', 'id_motivo', 'id_usuario',
        'tipo_movimiento', 'cantidad',
        'stock_anterior', 'stock_nuevo',
        'fecha_movimiento', 'observaciones',
    ];

    public function getTable(): string   { return $this->table; }
    public function getKeyName(): string  { return $this->primaryKey; }
    public function getFillable(): array  { return $this->fillable; }
    public function getHidden(): array    { return []; }
}
