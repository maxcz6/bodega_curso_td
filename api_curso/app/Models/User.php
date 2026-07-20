<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'nombres',
        'username',
        'password',
        'estado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'estado' => 'boolean',
    ];



    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_usuario', 'id_usuario');
    }

    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'id_usuario', 'id_usuario');
    }
}
