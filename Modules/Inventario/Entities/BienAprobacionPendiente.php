<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Users\Models\User;
use Modules\Inventario\Entities\Bien;

class BienAprobacionPendiente extends Model
{
    protected $table = 'bienes_aprobacion_pendiente';

    protected $fillable = [
        'bien_id',
        'tipo_objeto',
        'campo',
        'valor_anterior',
        'valor_nuevo',
        'usuario_id',
        'estado',
    ];

    public function bien()
    {
        return $this->belongsTo(Bien::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
