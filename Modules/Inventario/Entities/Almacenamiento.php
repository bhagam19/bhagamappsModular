<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Almacenamiento extends Model
{
    use HasFactory;

    protected $table = 'almacenamientos';

    protected $fillable = ['nombre'];

    public function bienes()
    {
        return $this->hasMany(Bien::class, 'almacenamiento_id');
    }
}
