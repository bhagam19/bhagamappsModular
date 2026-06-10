<?php

namespace Modules\Inventario\Http\Controllers;

use App\Http\Controllers\Controller;

class ResponsablesController extends Controller
{
    public function index()
    {
        return view('inventario::responsables.index');
    }
}
