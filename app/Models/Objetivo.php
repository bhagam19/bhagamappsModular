<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Objetivo extends Model
{
    use SoftDeletes;

    protected $table = 'objetivos';

    protected $fillable = ['proceso_id', 'codigo', 'nombre', 'descripcion', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function proceso()
    {
        return $this->belongsTo(Proceso::class);
    }

    public function metas()
    {
        return $this->hasMany(Meta::class)->orderBy('codigo');
    }
}
