<?php

namespace Modules\Inventario\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Inventario\Entities\BienAprobacionPendiente;

class BapController extends Controller
{
    public function index()
    {
        // Traer todos los cambios pendientes (por ejemplo, con estado 'pendiente')
        $aprobacionesPendientes = BienAprobacionPendiente::where('estado', 'pendiente')->paginate(20);

        return view('inventario::bap.bapindex', compact('aprobacionesPendientes'));
    }
}
