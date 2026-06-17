<?php

namespace App\Http\Controllers\Ppal;

use App\Http\Controllers\Controller;
use App\Models\Gestion;

class PlaneacionController extends Controller
{
    public function index()
    {
        $gestiones = Gestion::with([
            'procesos.objetivos.metas.indicadores',
        ])
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        return view('planeacion.index', compact('gestiones'));
    }
}
