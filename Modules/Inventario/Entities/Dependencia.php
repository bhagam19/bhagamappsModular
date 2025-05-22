<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dependencia extends Model
{
    use HasFactory;

    protected $table = 'dependencias';

    protected $fillable = [
        'nom_dependencias',
        'cod_ubicacion',
        'usuario_id',
    ];

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'cod_ubicacion');
    }

    public function usuario()
    {
        return $this->belongsTo(\Modules\Users\Entities\User::class, 'usuario_id');
    }

    public function bienes()
    {
        return $this->hasMany(Bien::class, 'cod_dependencias');
    }
}
