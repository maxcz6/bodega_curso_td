<?php

namespace App\Models;

class MotivoMovimiento
{
    public bool $timestamps = false;

    protected string $table      = 'motivos_movimiento';
    protected string $primaryKey = 'id_motivo';
    protected array  $fillable   = ['nombre'];

    public function getTable(): string   { return $this->table; }
    public function getKeyName(): string  { return $this->primaryKey; }
    public function getFillable(): array  { return $this->fillable; }
    public function getHidden(): array    { return []; }
}
