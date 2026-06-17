<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meta extends Model
{
    use SoftDeletes;

    protected $table = 'metas';

    protected $fillable = [
        'objetivo_id', 'componente_id', 'codigo', 'nombre',
        'descripcion', 'unidad', 'valor_objetivo', 'activo',
    ];

    protected $casts = [
        'activo'         => 'boolean',
        'valor_objetivo' => 'decimal:2',
    ];

    public function objetivo()
    {
        return $this->belongsTo(Objetivo::class);
    }

    public function componente()
    {
        return $this->belongsTo(Componente::class);
    }

    public function indicadores()
    {
        return $this->belongsToMany(Indicador::class, 'meta_indicador');
    }
}
