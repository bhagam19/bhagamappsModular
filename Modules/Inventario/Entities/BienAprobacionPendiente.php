<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BienAprobacionPendiente extends Model
{
    use HasFactory;

    protected $table = 'bienes_aprobacion_pendiente';

    protected $fillable = [
        'bien_id',
        'nom_bien',
        'detalle_del_bien',
        'serie_del_bien',
        'origen_del_bien',
        'fecha_adquisicion',
        'precio',
        'cant_bien',
        'cod_categoria',
        'cod_dependencias',
        'usuario_id',
        'cod_almacenamiento',
        'cod_estado',
        'cod_mantenimiento',
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
