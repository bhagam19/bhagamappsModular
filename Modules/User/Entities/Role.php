<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Database\Factories\RoleFactory;

class Role extends Model
{
    use HasFactory;

    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }

    protected $fillable = [
        'nombre',
        'descripcion',
        'app_id',
    ];

    // Relación: un rol tiene muchos users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }

    public function hasPermission($permissionName)
    {
        return $this->permissions->contains('nombre', $permissionName);
    }
}
