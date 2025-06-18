<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Users\Models\User;
use Modules\Inventario\Entities\{
    Bien,
    Estado,
    Categoria,
    Dependencia,
};

class BienAprobacionPendiente extends Model
{
    protected $table = 'bienes_aprobacion_pendiente';

    protected $fillable = [
        'bien_id',
        'tipo_objeto',
        'campo',
        'valor_anterior',
        'valor_nuevo',
        'dependencia_id',
        'estado',
    ];

    public function bien()
    {
        return $this->belongsTo(Bien::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'dependencia_id');
    }

    public function usuarioResponsable()
    {
        return $this->dependencia ? $this->dependencia->usuario : null;
    }

    public function obtenerNombreValor($campo, $valor)
    {
        // Puedes personalizar más casos si tienes más tablas
        return match ($campo) {
            'estado_id' => Estado::find($valor)?->nombre ?? $valor,
            'categoria_id' => Categoria::find($valor)?->nombre ?? $valor,
            'dependencia_id' => Dependencia::find($valor)?->nombre ?? $valor,
            default => $valor,
        };
    }

    public function getValorAnteriorNombreAttribute()
    {
        return $this->obtenerNombreValor($this->campo, $this->valor_anterior);
    }

    public function getValorNuevoNombreAttribute()
    {
        return $this->obtenerNombreValor($this->campo, $this->valor_nuevo);
    }

    public function getCampoNombreAttribute()
    {
        return str($this->campo)->endsWith('_id')
            ? str($this->campo)->beforeLast('_id')
            : $this->campo;
    }

    public function valorAnteriorCategoria()
    {
        return $this->belongsTo(Categoria::class, 'valor_anterior');
    }

    public function valorNuevoCategoria()
    {
        return $this->belongsTo(Categoria::class, 'valor_nuevo');
    }

    public function valorAnteriorDependencia()
    {
        return $this->belongsTo(Dependencia::class, 'valor_anterior');
    }

    public function valorNuevoDependencia()
    {
        return $this->belongsTo(Dependencia::class, 'valor_nuevo');
    }

    public function valorAnteriorEstado()
    {
        return $this->belongsTo(Estado::class, 'valor_anterior');
    }

    public function valorNuevoEstado()
    {
        return $this->belongsTo(Estado::class, 'valor_nuevo');
    }
}
