<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MantenimientoProgramado extends Model
{
    use HasFactory;

    protected $table = 'mantenimientos_programados';

    protected $fillable = [
        'bien_id',
        'fecha_programada',
        'descripcion',
        'estado'
    ];

    public function bien()
    {
        return $this->belongsTo(Bien::class, 'bien_id');
    }
}
