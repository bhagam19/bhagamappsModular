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
        'campo_modificado',  // nombre del campo que se modificó
        'valor_anterior',
        'valor_nuevo',
        'usuario_id',        // quien realizó la modificación
        'aprobado_por',      // quien aprobó la modificación
        'fecha_modificacion',
    ];

    protected $casts = [
        'fecha_modificacion' => 'datetime',
    ];

    public function bien()
    {
        return $this->belongsTo(Bien::class, 'bien_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function aprobadoPor()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }
}
