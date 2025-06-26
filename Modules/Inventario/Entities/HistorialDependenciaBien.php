<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Entities\User;

class HistorialDependenciaBien extends Model
{
    use HasFactory;

    protected $table = 'historial_dependencias_bienes';

    protected $fillable = [
        'bien_id',
        'dependencia_anterior_id',
        'dependencia_nueva_id',
        'user_id',        // quien hizo el cambio
        'aprobado_por',      // quien aprobó el cambio
        'fecha_modificacion'
    ];

    public function bien()
    {
        return $this->belongsTo(Bien::class, 'bien_id');
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'dependencia_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
