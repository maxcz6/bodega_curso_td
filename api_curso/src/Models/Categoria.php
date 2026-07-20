<?php

namespace App\Models;

class Categoria
{
    public bool $timestamps = false;

    protected string $table      = 'categorias';
    protected string $primaryKey = 'id_categoria';
    protected array  $fillable   = ['nombre', 'descripcion'];

    public function getTable(): string   { return $this->table; }
    public function getKeyName(): string  { return $this->primaryKey; }
    public function getFillable(): array  { return $this->fillable; }
    public function getHidden(): array    { return []; }
}
