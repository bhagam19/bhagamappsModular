<?php

namespace Modules\Inventario\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Inventario\Entities\HistorialEliminacionBien;

class HebController extends Controller
{
    public function index()
    {
        // Traer todas las eliminaciones 
        $eliminaciones = HistorialEliminacionBien::with(['bien', 'user', 'dependencia'])
            ->orderByDesc('created_at') // Opcional: para ver las más recientes primero
            ->paginate(20);

        return view('inventario::heb.hebindex', compact('eliminaciones'));
    }
}
