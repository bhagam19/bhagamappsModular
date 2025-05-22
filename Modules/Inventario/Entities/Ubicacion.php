<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ubicacion extends Model
{
    use HasFactory;

    protected $table = 'ubicaciones';

    protected $fillable = ['nom_ubicacion'];

    public function dependencias()
    {
        return $this->hasMany(Dependencia::class, 'cod_ubicacion');
    }
}
