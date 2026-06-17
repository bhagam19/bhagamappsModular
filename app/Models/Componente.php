<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Componente extends Model
{
    use SoftDeletes;

    protected $table = 'componentes';

    protected $fillable = ['proceso_id', 'codigo', 'nombre', 'descripcion', 'orden', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function proceso()
    {
        return $this->belongsTo(Proceso::class);
    }

    public function metas()
    {
        return $this->hasMany(Meta::class)->orderBy('codigo');
    }

    public function indicadores()
    {
        return $this->hasMany(Indicador::class)->orderBy('codigo');
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class)->orderBy('codigo');
    }
}
