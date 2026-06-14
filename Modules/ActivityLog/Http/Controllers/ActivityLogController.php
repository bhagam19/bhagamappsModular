<?php

namespace Modules\ActivityLog\Http\Controllers;

use App\Http\Controllers\Controller;

class ActivityLogController extends Controller
{
    public function index(): \Illuminate\Contracts\View\View
    {
        if (!auth()->user()->isAdminPrincipal()) {
            abort(403, 'Esta sección está reservada para el Administrador Principal.');
        }

        return view('activitylog::index');
    }
}
