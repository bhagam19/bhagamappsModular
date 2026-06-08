<?php

namespace Modules\Inventario\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Inventario\Entities\Bien;

use App\Http\Controllers\Controller;

class BienController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $bienes = Bien::all();    
        return view('inventario::bienes.index', compact('bienes'));

    }  
    
}
