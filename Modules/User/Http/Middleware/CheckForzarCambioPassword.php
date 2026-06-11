<?php

namespace Modules\User\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckForzarCambioPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->forzar_cambio_password) {
            $allowed = [
                'logout',
                'user/profile',
                'user/password',
                'user/profile-photo',
                'livewire/',
            ];

            foreach ($allowed as $prefix) {
                if ($request->is($prefix) || $request->is($prefix . '/*')) {
                    return $next($request);
                }
            }

            return redirect('/user/profile')
                ->with('status', 'Debes cambiar tu contraseña antes de continuar.');
        }

        return $next($request);
    }
}
