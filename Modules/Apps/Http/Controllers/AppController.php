<?php

namespace Modules\Apps\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Apps\Entities\App;

class AppController extends Controller
{
    public function index()
    {
        return view('apps::admin.index');
    }
}
