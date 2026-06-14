<?php

namespace Modules\AdminSistema\Http\Controllers;

use Illuminate\Routing\Controller;

class ImportarSnapshotController extends Controller
{
    public function index(): \Illuminate\Contracts\View\View
    {
        // SNAP-002: verificación adicional en capa de controlador
        if (!auth()->user()->isAdminPrincipal()) {
            abort(403, 'Solo el Administrador Principal puede importar Snapshots.');
        }

        return view('adminsistema::backups.importar');
    }
}
