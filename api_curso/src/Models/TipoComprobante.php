<?php

namespace App\Models;

class TipoComprobante
{
    public bool $timestamps = false;

    protected string $table      = 'tipo_comprobante';
    protected string $primaryKey = 'id_tipo_comprobante';
    protected array  $fillable   = ['nombre'];

    public function getTable(): string   { return $this->table; }
    public function getKeyName(): string  { return $this->primaryKey; }
    public function getFillable(): array  { return $this->fillable; }
    public function getHidden(): array    { return []; }
}
