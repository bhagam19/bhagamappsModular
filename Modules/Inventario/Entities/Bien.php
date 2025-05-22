<?php

namespace Modules\Inventario\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bien extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom_bien',
        'detalle_del_bien',
        'serie_del_bien',
        'origen_del_bien',
        'fecha_adquisicion',
        'precio',
        'cant_bien',
        'cod_categoria',
        'cod_dependencias',
        'usuario_id',
        'cod_almacenamiento',
        'cod_estado',
        'cod_mantenimiento',
        'observaciones',
    ];

    protected $table = 'bienes';

    // Relaciones
    public function categoria()
    {
        return $this->belongsTo(CategoriaDeBien::class, 'cod_categoria');
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'cod_dependencias');
    }

    public function almacenamiento()
    {
        return $this->belongsTo(Almacenamiento::class, 'cod_almacenamiento');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoDelBien::class, 'cod_estado');
    }

    public function mantenimiento()
    {
        return $this->belongsTo(Mantenimiento::class, 'cod_mantenimiento');
    }

    public function responsable()
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

    public function detalle()
    {
        return $this->hasOne(DetalleDeBien::class);
    }

    public function mantenimientosProgramados()
    {
        return $this->hasMany(MantenimientoProgramado::class);
    }
}
