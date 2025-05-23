<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BienResponsable extends Model
{
    use HasFactory;

    protected $table = 'bienes_responsables';

    protected $fillable = [
        'bien_id',
        'usuario_id',
        'observaciones',
        'fecha_asignacion',
        'fecha_retiro'
    ];

    public function bien()
    {
        return $this->belongsTo(Bien::class, 'bien_id');
    }

    public function usuario()
    {
        return $this->belongsTo(\Modules\Users\Entities\User::class, 'usuario_id');
    }
}
