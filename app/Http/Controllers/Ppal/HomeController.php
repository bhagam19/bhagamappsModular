<?php

namespace App\Http\Controllers\Ppal;

use App\Http\Controllers\Controller;
use Modules\Apps\Entities\App;

class HomeController extends Controller
{
    public function index()
    {
        $apps = auth()->user()->apps()->wherePivot('activo', true)->where('habilitada', true)->get();
        return view('ppal.index', compact('apps'));
    }
}
