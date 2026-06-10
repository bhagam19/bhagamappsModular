<?php

namespace App\Models\Inventario;

use App\Enums\Inventario\EstadoBien;
use App\Enums\Inventario\EstadoMantenimiento;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'categoria_bien_id',
    'ubicacion_fisica_id',
    'nombre',
    'descripcion',
    'codigo_institucional',
    'codigo_origen',
    'estado_bien',
    'estado_mantenimiento',
    'fecha_adquisicion',
    'valor_adquisicion',
    'observaciones',
])]
class Bien extends Model
{
    protected $table = 'bienes';

    protected function casts(): array
    {
        return [
            'estado_bien'          => EstadoBien::class,
            'estado_mantenimiento' => EstadoMantenimiento::class,
            'fecha_adquisicion'    => 'date',
            'valor_adquisicion'    => 'decimal:2',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaBien::class, 'categoria_bien_id');
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(UbicacionFisica::class, 'ubicacion_fisica_id');
    }

    public function detalle(): HasOne
    {
        return $this->hasOne(DetalleBien::class, 'bien_id');
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(ImagenBien::class, 'bien_id');
    }

    public function responsables(): HasMany
    {
        return $this->hasMany(BienResponsable::class, 'bien_id')
            ->orderByDesc('fecha_asignacion');
    }

    public function responsableActual(): HasOne
    {
        return $this->hasOne(BienResponsable::class, 'bien_id')
            ->whereNull('fecha_retiro');
    }
}
