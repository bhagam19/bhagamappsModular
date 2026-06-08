<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mantenimiento extends Model
{
    use HasFactory;

    protected $table = 'mantenimientos';

    protected $fillable = ['nom_mantenimiento'];

    public function bienes()
    {
        return $this->hasMany(Bien::class, 'cod_mantenimiento');
    }
}
