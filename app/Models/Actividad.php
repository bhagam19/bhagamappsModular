<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Actividad extends Model
{
    use SoftDeletes;

    protected $table = 'actividades';

    protected $fillable = [
        'meta_id', 'componente_id', 'codigo', 'nombre', 'descripcion',
        'estado', 'avance_manual', 'avance_calculado',
        'fecha_inicio', 'fecha_fin', 'activo',
    ];

    protected $casts = [
        'activo'           => 'boolean',
        'avance_manual'    => 'integer',
        'avance_calculado' => 'integer',
        'fecha_inicio'     => 'date',
        'fecha_fin'        => 'date',
    ];

    public function meta()
    {
        return $this->belongsTo(Meta::class);
    }

    public function componente()
    {
        return $this->belongsTo(Componente::class);
    }

    public function tareas()
    {
        return $this->hasMany(Tarea::class)->orderBy('codigo');
    }

    public function calcularAvance(): int
    {
        $tareas = $this->tareas()->where('activo', true)->get();
        if ($tareas->isEmpty()) {
            return 0;
        }
        return (int) round($tareas->avg('avance'));
    }
}
