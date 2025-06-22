<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Users\Models\User;

class HistorialModificacionBien extends Model
{
    use HasFactory;

    protected $table = 'historial_modificaciones_bienes';

    protected $fillable = [
        'bien_id',
        'tipo_objeto',       // 'bien' o 'detalle'
        'campo',  // nombre del campo que se modificó
        'valor_anterior',
        'valor_nuevo',
        'dependencia_id',     // dependencia afectada
        'aprobado_por',      // quien aprobó la modificación        
        'estado',
    ];

    public function bien()
    {
        return $this->belongsTo(Bien::class, 'bien_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'dependencia_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function almacenamiento()
    {
        return $this->belongsTo(Almacenamiento::class, 'almacenamiento_id');
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

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }
}
