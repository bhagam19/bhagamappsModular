<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Entities\User;
use Modules\User\Entities\Role;
use Modules\Inventario\Entities\Dependencia;

class Tarea extends Model
{
    use SoftDeletes;

    protected $table = 'tareas';

    protected $fillable = [
        'actividad_id', 'responsable_tipo', 'responsable_id',
        'codigo', 'nombre', 'descripcion',
        'estado', 'avance',
        'fecha_inicio', 'fecha_fin', 'activo',
    ];

    protected $casts = [
        'activo'      => 'boolean',
        'avance'      => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    public function actividad()
    {
        return $this->belongsTo(Actividad::class);
    }

    public function responsable(): User|Role|Dependencia|null
    {
        return match ($this->responsable_tipo) {
            'usuario'     => User::find($this->responsable_id),
            'rol'         => Role::find($this->responsable_id),
            'dependencia' => Dependencia::find($this->responsable_id),
            default       => null,
        };
    }

    public function getNombreResponsableAttribute(): string
    {
        $resp = $this->responsable();
        if (! $resp) {
            return '—';
        }
        return match ($this->responsable_tipo) {
            'usuario'     => $resp->nombres . ' ' . $resp->apellidos,
            'rol'         => $resp->nombre,
            'dependencia' => $resp->nombre,
            default       => '—',
        };
    }
}
