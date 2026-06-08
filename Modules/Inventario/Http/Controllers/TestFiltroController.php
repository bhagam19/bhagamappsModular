<?php

namespace Modules\Inventario\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class TestFiltroController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        return view('inventario::bienes.test-filtro');

    }  
    
}
