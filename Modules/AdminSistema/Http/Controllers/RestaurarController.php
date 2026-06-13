<?php

namespace Modules\AdminSistema\Http\Controllers;

use Illuminate\Routing\Controller;

class RestaurarController extends Controller
{
    public function index(): \Illuminate\Contracts\View\View
    {
        // RESTORE-WEB-005: verificación adicional en capa de controlador
        if (!auth()->user()->isAdminPrincipal()) {
            abort(403, 'Solo el Administrador Principal puede acceder a la restauración.');
        }

        return view('adminsistema::backups.restaurar');
    }
}
