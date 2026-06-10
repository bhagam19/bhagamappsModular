<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Origen extends Model
{
    use HasFactory;

    protected $table = 'origenes';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];
}
