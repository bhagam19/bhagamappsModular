<?php

namespace App\Http\Controllers\Auth;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\Request;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        return redirect()->intended('/'); // ← cambia aquí la ruta deseada
    }
}