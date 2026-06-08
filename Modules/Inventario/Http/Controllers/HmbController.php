<?php

namespace Modules\Inventario\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Inventario\Entities\HistorialModificacionBien;

class HmbController extends Controller
{
    public function index()
    {
        // Traer todas las modificaciones de bienes 
        $modificacion = HistorialModificacionBien::with(['bien', 'user', 'dependencia'])
            ->orderByDesc('created_at') // Opcional: para ver las más recientes primero
            ->paginate(20);

        return view('inventario::hmb.hmbindex', compact('modificacion'));
    }
}
