<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class AuditoriaPassword extends Model
{
    public $timestamps = false;

    protected $table = 'auditoria_passwords';

    protected $fillable = [
        'usuario_afectado_id',
        'administrador_id',
        'accion',
        'fecha_hora',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public function usuarioAfectado()
    {
        return $this->belongsTo(User::class, 'usuario_afectado_id');
    }

    public function administrador()
    {
        return $this->belongsTo(User::class, 'administrador_id');
    }
}
