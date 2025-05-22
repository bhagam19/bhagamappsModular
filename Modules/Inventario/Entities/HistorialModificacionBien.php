<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HistorialModificacionBien extends Model
{
    use HasFactory;

    protected $table = 'historial_modificaciones_bienes';

    protected $fillable = [
        'bien_id',
        'usuario_id',
        'campo',
        'valor_anterior',
        'valor_nuevo',
        'fecha',
        'observaciones'
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
