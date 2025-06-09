<?php

namespace Modules\Users\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

use Modules\Inventario\Entities\Bien;
use Modules\Apps\Entities\App;
use Modules\Inventario\Entities\Dependencia;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombres',
        'apellidos',
        'userID',
        'email',
        'password',
        'role_id', // ← ahora se utiliza esta columna en lugar de 'rol'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Retorna el nombre completo del usuario.
     */
    public function getNombreCompletoAttribute()
    {
        return $this->nombres . ' ' . $this->apellidos;
    }

    /**
     * Relación muchos a muchos con el modelo Grupo (como docente).
     */
    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'docente_grupo');
    }

    /**
     * Relación muchos a uno con el modelo Role.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function dependencias()
{
    return $this->hasMany(Dependencia::class, 'usuario_id');
}

    /**
     * Verifica si el usuario tiene un rol específico por nombre.
     */
    public function hasRole($roleNombre)
    {
        return $this->role && $this->role->nombre === $roleNombre;
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function hasPermission($slug)
    {
        // permiso directo
        if ($this->permissions()->where('slug', $slug)->exists()) {
            return true;
        }

        // permiso por rol
        if ($this->role && $this->role->permissions()->where('slug', $slug)->exists()) {
            return true;
        }

        return false;
    }

    public function adminlte_desc()
    {
        // Retorna descripción, por ejemplo el email
        return $this->email;
    }

    public function adminlte_profile_url()
    {
        // Retorna la URL que usarás para mostrar el perfil del usuario
        // Si no tienes ruta, puedes devolver null o la ruta de logout para evitar errores

        return route('profile.show'); // Cambia 'profile.show' por la ruta real de perfil
    }

    public function adminlte_image()
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : asset('images/default-avatar.png');
    }

    public function bienes()
    {
        return $this->hasMany(Bien::class, 'usuario_id');
    }

    public function apps()
    {
        return $this->belongsToMany(App::class, 'app_user', 'user_id', 'app_id');
    }
}
