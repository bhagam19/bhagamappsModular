<?php

namespace Modules\Inventario\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Inventario\Entities\BienAprobacionPendiente;

class CambioPendienteController extends Controller
{
    public function index()
    {
        // Traer todos los cambios pendientes (por ejemplo, con estado 'pendiente')
        $cambios = BienAprobacionPendiente::where('estado', 'pendiente')->paginate(20);

        return view('inventario::cambios-pendientes.index', compact('cambios'));
    }
}
