<?php

namespace Modules\Apps\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Entities\User;

class App extends Model
{
    use HasFactory;

    protected $table = 'apps';

    protected $fillable = [
        'nombre',
        'ruta',
        'imagen',
        'user_id',
        'habilitada',
    ];

    public function user()
    {
        return $this->belongsToMany(User::class, 'app_user', 'app_id', 'user_id');
    }
}
