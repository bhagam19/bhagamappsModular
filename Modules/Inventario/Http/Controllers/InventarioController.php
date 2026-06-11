<?php

namespace Modules\Inventario\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class InventarioController extends Controller
{
    public function dashboard(): View
    {
        return view('inventario::dashboard.index');
    }
}
