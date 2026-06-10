<?php

namespace Modules\Inventario\Http\Controllers;

use Illuminate\Routing\Controller;

class MantenimientosProgramadosController extends Controller
{
    public function index()
    {
        return view('inventario::mantenimientos.index');
    }
}
