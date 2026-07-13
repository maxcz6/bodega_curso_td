<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoComprobante extends Model
{
    
    protected $table = 'tipo_comprobante';
    protected $primaryKey = 'id_tipo_comprobante';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_tipo_comprobante',
        'nombre',
    ];

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_tipo_comprobante', 'id_tipo_comprobante');
    }

    /**
     * Override: TipoComprobante.id_tipo_comprobante is NOT auto-generated, so include it in sync
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
