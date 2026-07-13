<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class MotivoMovimiento extends Model
{
    
    protected $table = 'motivos_movimiento';
    protected $primaryKey = 'id_motivo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_motivo',
        'nombre',
    ];

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'id_motivo', 'id_motivo');
    }

    /**
     * Override: MotivoMovimiento.id_motivo is NOT auto-generated, so include it in sync
     */
    protected function getSyncExcludedFields(): array
    {
        return [
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }
}
