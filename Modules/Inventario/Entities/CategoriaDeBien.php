<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoriaDeBien extends Model
{
    use HasFactory;

    protected $table = 'categorias_de_bienes';

    protected $fillable = ['nom_categoria'];

    public function bienes()
    {
        return $this->hasMany(Bien::class, 'cod_categoria');
    }
}
