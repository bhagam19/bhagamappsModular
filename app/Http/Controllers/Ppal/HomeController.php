<?php

namespace App\Http\Controllers\Ppal;

use App\Http\Controllers\Controller;
use Modules\Apps\Entities\App;

class HomeController extends Controller
{
    public function index()
    {
        $apps = App::visiblesPara(auth()->user());
        return view('ppal.index', compact('apps'));
    }
}
