<?php

namespace App\Models;

class Cliente
{
    public bool $timestamps = false;

    protected string $table      = 'clientes';
    protected string $primaryKey = 'id_cliente';
    protected array  $fillable   = [
        'nombres', 'dni_ruc', 'telefono', 'direccion',
    ];

    public function getTable(): string   { return $this->table; }
    public function getKeyName(): string  { return $this->primaryKey; }
    public function getFillable(): array  { return $this->fillable; }
    public function getHidden(): array    { return []; }
}
