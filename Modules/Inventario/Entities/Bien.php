<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Modules\Users\Models\User;

class Bien extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'serie',
        'origen',
        'fechaAdquisicion',
        'precio',
        'cantidad',
        'categoria_id',
        'dependencias_id',
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

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }    

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'dependencias_id');
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

    public function historialUbicaciones()
    {
        return $this->hasMany(HistorialUbicacionBien::class);
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
}
