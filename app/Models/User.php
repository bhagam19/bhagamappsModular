<?php

namespace App\Models;

// LEGACY — No utilizado por el sistema de autenticación activo de IEE.
// El modelo activo es Modules\User\Entities\User (ver config/auth.php).
// Pendiente de eliminación — ver AUDIT-CORE-DEADCODE-001 / IMPL-CORE-CLEANUP-001.
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}
