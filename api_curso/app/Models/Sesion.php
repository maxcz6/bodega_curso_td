<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sesion extends Model
{
    // La propiedad $fillable le dice a Laravel qué campos de la tabla
    // están permitidos para ser guardados masivamente (asignación masiva).
    // Esto es por seguridad.
    protected $fillable = [
        'semana',
        'fecha',
        'indicador_logro',
        'contenidos',
        'sesion_aprendizaje',
        'indicador_logro_sesion'
    ];
}
