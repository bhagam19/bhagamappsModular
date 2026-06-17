<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proceso extends Model
{
    use SoftDeletes;

    protected $table = 'procesos';

    protected $fillable = ['gestion_id', 'codigo', 'nombre', 'descripcion', 'orden', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function gestion()
    {
        return $this->belongsTo(Gestion::class);
    }

    public function componentes()
    {
        return $this->hasMany(Componente::class)->orderBy('orden');
    }
}
