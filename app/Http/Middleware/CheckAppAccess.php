<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Apps\Entities\App;
use Symfony\Component\HttpFoundation\Response;

class CheckAppAccess
{
    public function handle(Request $request, Closure $next, string $slug): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'No tienes acceso a este módulo.');
        }

        if (! App::visiblesPara($user)->contains('slug', $slug)) {
            abort(403, 'No tienes acceso al módulo "' . $slug . '".');
        }

        return $next($request);
    }
}
