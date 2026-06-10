<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Entities\User;

class MantenimientoProgramado extends Model
{
    use HasFactory;

    protected $table = 'mantenimientos_programados';

    protected $fillable = [
        'bien_id',
        'user_id',
        'tipo',
        'titulo',
        'descripcion',
        'fecha_programada',
        'fecha_realizada',
        'estado',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_realizada'  => 'date',
    ];

    public function bien()
    {
        return $this->belongsTo(Bien::class, 'bien_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function esPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }
}
