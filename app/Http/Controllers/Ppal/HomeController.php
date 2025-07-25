<?php

namespace App\Http\Controllers\Ppal;

use App\Http\Controllers\Controller;
use Modules\Apps\Entities\App;

class HomeController extends Controller
{
    public function index() 
    {
        $apps = App::where('user_id', auth()->id())->get();
        //dd($apps); // Esto detendrá la ejecución y mostrará el contenido
        return view('ppal.index', compact('apps'));
    }
}
