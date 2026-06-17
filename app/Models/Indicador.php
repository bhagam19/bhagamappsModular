<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Indicador extends Model
{
    use SoftDeletes;

    protected $table = 'indicadores';

    protected $fillable = [
        'componente_id', 'codigo', 'nombre', 'descripcion',
        'formula', 'unidad', 'frecuencia', 'tipo', 'fuente_dato', 'activo',
    ];

    protected $casts = ['activo' => 'boolean'];

    public function componente()
    {
        return $this->belongsTo(Componente::class);
    }

    public function metas()
    {
        return $this->belongsToMany(Meta::class, 'meta_indicador');
    }
}
