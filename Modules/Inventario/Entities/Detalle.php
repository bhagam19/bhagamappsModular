<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Detalle extends Model
{
    use HasFactory;

    protected $table = 'detalles';

    protected $fillable = [
        'bien_id',
        'car_especial',
        'tamano',
        'material',
        'color',
        'marca',
        'otra',
    ];

    public function bien()
    {
        return $this->belongsTo(Bien::class, 'bien_id');
    }
}
