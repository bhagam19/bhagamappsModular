<?php

namespace App\Models\Inventario;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'bien_id',
    'user_id',
    'fecha_asignacion',
    'fecha_retiro',
    'asignado_por_user_id',
])]
class BienResponsable extends Model
{
    protected $table = 'bienes_responsables';

    protected function casts(): array
    {
        return [
            'fecha_asignacion' => 'date',
            'fecha_retiro'     => 'date',
        ];
    }

    public function bien(): BelongsTo
    {
        return $this->belongsTo(Bien::class, 'bien_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por_user_id');
    }

    public function scopeVigente(Builder $query): Builder
    {
        return $query->whereNull('fecha_retiro');
    }

    public function estaVigente(): bool
    {
        return $this->fecha_retiro === null;
    }
}
