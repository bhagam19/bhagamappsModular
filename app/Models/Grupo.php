<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $fillable = ['nombre'];

    // Relación con usuarios
    public function estudiantes()
    {
        return $this->hasMany(User::class);
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'grupo_user');
    }

    public function docentes()
    {
        return $this->belongsToMany(User::class, 'docente_grupo', 'grupo_id', 'user_id');
    }

}
