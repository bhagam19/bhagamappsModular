<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HistorialUbicacionBien extends Model
{
    use HasFactory;

    protected $table = 'historial_ubicaciones_bienes';

    protected $fillable = [
        'bien_id',
        'dependencia_id',
        'usuario_id',
        'fecha',
        'observaciones'
    ];

    public function bien()
    {
        return $this->belongsTo(Bien::class, 'bien_id');
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'dependencia_id');
    }

    public function usuario()
    {
        return $this->belongsTo(\Modules\Users\Entities\User::class, 'usuario_id');
    }
}
