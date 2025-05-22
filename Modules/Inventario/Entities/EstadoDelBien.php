<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EstadoDelBien extends Model
{
    use HasFactory;

    protected $table = 'estado_del_bien';

    protected $fillable = ['nom_estado'];

    public function bienes()
    {
        return $this->hasMany(Bien::class, 'cod_estado');
    }
}
