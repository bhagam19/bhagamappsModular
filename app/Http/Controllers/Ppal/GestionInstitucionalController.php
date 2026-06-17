<?php

namespace App\Http\Controllers\Ppal;

use App\Http\Controllers\Controller;
use App\Models\Gestion;

class GestionInstitucionalController extends Controller
{
    public function arbol()
    {
        $gestiones = Gestion::with(['procesos.componentes'])
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        return view('gestion.arbol', compact('gestiones'));
    }
}
