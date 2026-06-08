<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use Modules\Inventario\Entities\{
    Bien,
    Dependencia
};

class HistorialEliminacionBien extends Model
{
    protected $table = 'historial_eliminaciones_bienes';

    protected $fillable = [
        'bien_id',
        'dependencia_id',
        'user_id',
        'aprobado_por',
        'estado',
        'motivo'
    ];

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'dependencia_id');
    }

    public function bien()
    {
        return $this->belongsTo(Bien::class, 'bien_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }
}
