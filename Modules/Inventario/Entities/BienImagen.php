<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BienImagen extends Model
{
    use HasFactory;

    protected $table = 'bienes_imagenes';

    protected $fillable = [
        'bien_id',
        'ruta',
        'descripcion'
    ];

    public function bien()
    {
        return $this->belongsTo(Bien::class, 'bien_id');
    }
}
