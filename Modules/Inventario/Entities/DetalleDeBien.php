<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetalleDeBien extends Model
{
    use HasFactory;

    protected $table = 'detalles_de_bienes';

    protected $fillable = [
        'car_especial',
        'tamano',
        'material',
        'color',
        'marca',
        'otra',
    ];
}
