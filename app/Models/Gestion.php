<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gestion extends Model
{
    use SoftDeletes;

    protected $table = 'gestiones';

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'orden', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function procesos()
    {
        return $this->hasMany(Proceso::class)->orderBy('orden');
    }
}
