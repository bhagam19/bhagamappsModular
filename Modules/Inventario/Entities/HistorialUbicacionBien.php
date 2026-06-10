<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Entities\User;

class HistorialUbicacionBien extends Model
{
    use HasFactory;

    protected $table = 'historial_ubicaciones_bienes';

    protected $fillable = [
        'bien_id',
        'ubicacion_origen_id',
        'ubicacion_destino_id',
        'user_id',
        'fecha_movimiento',
        'observaciones',
    ];

    protected $casts = [
        'fecha_movimiento' => 'datetime',
    ];

    public function bien()
    {
        return $this->belongsTo(Bien::class, 'bien_id');
    }

    public function ubicacionOrigen()
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_origen_id');
    }

    public function ubicacionDestino()
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_destino_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
