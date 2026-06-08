<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'categoria', 'slug'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function user()
    {
        return $this->belongsToMany(User::class);
    }
}
