<?php

namespace Modules\Inventario\Http\Controllers;

use App\Http\Controllers\Controller;

class CatalogosController extends Controller
{
    public function categorias()
    {
        return view('inventario::catalogos.categorias');
    }

    public function dependencias()
    {
        return view('inventario::catalogos.dependencias');
    }

    public function ubicaciones()
    {
        return view('inventario::catalogos.ubicaciones');
    }

    public function estados()
    {
        return view('inventario::catalogos.estados');
    }

    public function origenes()
    {
        return view('inventario::catalogos.origenes');
    }

    public function almacenamientos()
    {
        return view('inventario::catalogos.almacenamientos');
    }

    public function mantenimientos()
    {
        return view('inventario::catalogos.mantenimientos');
    }
}
