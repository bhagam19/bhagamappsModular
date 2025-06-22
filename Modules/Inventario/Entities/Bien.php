<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

use Modules\Users\Models\User;

class Bien extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'serie',
        'origen',
        'fecha_adquisicion',
        'precio',
        'cantidad',
        'categoria_id',
        'dependencia_id',
        'ubicacion_id',
        'usuario_id',
        'almacenamiento_id',
        'estado_id',
        'mantenimiento_id',
        'observaciones',
    ];

    protected $table = 'bienes';

    // Relaciones

    public function detalle()
    {
        return $this->hasOne(Detalle::class, 'bien_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'dependencia_id');
    }

    public function almacenamiento()
    {
        return $this->belongsTo(Almacenamiento::class, 'almacenamiento_id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function mantenimiento()
    {
        return $this->belongsTo(Mantenimiento::class, 'mantenimiento_id');
    }

    public function responsables()
    {
        return $this->hasMany(BienResponsable::class);
    }

    public function historialDependencias()
    {
        return $this->hasMany(HistorialDependenciaBien::class);
    }

    public function historialModificaciones()
    {
        return $this->hasMany(HistorialModificacionBien::class);
    }

    public function imagenes()
    {
        return $this->hasMany(BienImagen::class);
    }

    public function mantenimientosProgramados()
    {
        return $this->hasMany(MantenimientoProgramado::class);
    }

    public function getDisplayValue($key)
    {
        // Mapeo de campos a relaciones y atributos representativos
        $relaciones = [
            'usuario_id'        => ['rel' => 'usuario',        'campo' => fn($u) => $u?->nombres . ' ' . $u?->apellidos],
            'categoria_id'      => ['rel' => 'categoria',      'campo' => fn($c) => $c?->nombre],
            'dependencia_id'    => ['rel' => 'dependencia',    'campo' => fn($d) => $d?->nombre],
            'almacenamiento_id' => ['rel' => 'almacenamiento', 'campo' => fn($a) => $a?->nombre],
            'estado_id'         => ['rel' => 'estado',         'campo' => fn($e) => $e?->nombre],
            'mantenimiento_id'  => ['rel' => 'mantenimiento',  'campo' => fn($m) => $m?->nombre],
        ];

        // Si es un campo de relación, retorna el valor representativo
        if (array_key_exists($key, $relaciones)) {
            $rel = $relaciones[$key]['rel'];
            $campo = $relaciones[$key]['campo'];
            return $campo($this->$rel) ?? '—';
        }

        $value = $this->$key;

        // Si es nulo
        if (is_null($value)) {
            return '—';
        }

        // Si el campo es precio, mostrar como pesos colombianos
        if ($key === 'precio' && is_numeric($value)) {
            return '$' . number_format((float) $value, 0, ',', '.');
        }

        // Si es una fecha (Carbon)
        if ($value instanceof \Carbon\Carbon) {
            return $value->format('d/m/Y');
        }

        // Si es un modelo relacionado
        if ($value instanceof \Illuminate\Database\Eloquent\Model) {
            return $value->nombre
                ?? $value->name
                ?? (method_exists($value, '__toString') ? (string)$value : $value->getKey());
        }

        // Si es un objeto genérico
        if (is_object($value)) {
            return method_exists($value, '__toString') ? (string)$value : '—';
        }

        // Si es un array
        if (is_array($value)) {
            return implode(', ', $value);
        }

        // Todo lo demás
        return (string) $value;
    }

    public function modificacionesPendientes()
    {
        return $this->hasMany(HistorialModificacionBien::class, 'bien_id');
    }

    public function tieneModificacionesPendientes(): bool
    {
        return $this->modificacionesPendientes()
            ->where('estado', 'pendiente')
            ->exists();
    }

    public function camposPendientes()
    {
        return $this->modificacionesPendientes()
            ->where('estado', 'pendiente')
            ->pluck('campo')
            ->unique()
            ->toArray();
    }

    public function eliminacionPendiente()
    {
        return $this->hasOne(HistorialEliminacionBien::class)->where('estado', 'pendiente');
    }
}
