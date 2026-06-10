<?php

namespace Modules\Inventario\Http\Controllers;

use Illuminate\Routing\Controller;

class UbicacionesHistorialController extends Controller
{
    public function index()
    {
        return view('inventario::ubicaciones.historial');
    }
}
