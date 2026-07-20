<?php

namespace App\Models;

class User
{
    public bool $timestamps = false;

    protected string $table      = 'usuarios';
    protected string $primaryKey = 'id_usuario';
    protected array  $fillable   = ['username', 'password', 'nombres', 'rol'];
    protected array  $hidden     = ['password', 'remember_token'];

    public function getTable(): string   { return $this->table; }
    public function getKeyName(): string  { return $this->primaryKey; }
    public function getFillable(): array  { return $this->fillable; }
    public function getHidden(): array    { return $this->hidden; }
}
