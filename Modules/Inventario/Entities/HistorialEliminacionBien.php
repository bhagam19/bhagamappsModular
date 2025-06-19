<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Users\Models\User;

class HistorialEliminacionBien extends Model
{
    protected $fillable = [
        'bien_id',
        'dependencia_id',
        'usuario_id',
        'aprobado_por',
        'estado',
        'motivo'
    ];

    public function bien()
    {
        return $this->belongsTo(Bien::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }
}
