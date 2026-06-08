<?php

namespace Modules\Apps\Entities;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;

class App extends Model
{
    use HasFactory;

    protected $table = 'apps';

    protected $fillable = [
        'nombre',
        'slug',
        'ruta',
        'descripcion',
        'imagen',
        'icono',
        'color',
        'orden',
        'habilitada',
    ];

    protected $casts = [
        'habilitada' => 'boolean',
        'orden'      => 'integer',
    ];

    public function user()
    {
        return $this->belongsToMany(User::class, 'app_user', 'app_id', 'user_id')
                    ->withPivot('activo')
                    ->withTimestamps();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'app_role', 'app_id', 'role_id')
                    ->withTimestamps();
    }

    /**
     * Retorna las apps visibles para un usuario dado.
     * Incluye apps asignadas al rol del usuario (app_role)
     * y apps asignadas individualmente al usuario (app_user).
     */
    public static function visiblesPara(User $user): Collection
    {
        return static::where('habilitada', true)
            ->where(function ($query) use ($user) {
                $query->whereHas('roles', function ($q) use ($user) {
                    $q->where('roles.id', $user->role_id);
                })->orWhereHas('user', function ($q) use ($user) {
                    $q->where('users.id', $user->id)
                      ->wherePivot('activo', true);
                });
            })
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();
    }
}
