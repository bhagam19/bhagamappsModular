<?php

namespace Modules\Apps\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Apps\Entities\App;

class AppController extends Controller
{
    public function index()
    {
        $apps = App::where('user_id', auth()->id())->get();
        return view('apps::index', compact('apps'));
    }
}
