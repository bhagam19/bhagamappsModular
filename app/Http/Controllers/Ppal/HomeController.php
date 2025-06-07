<?php

namespace App\Http\Controllers\Ppal;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('ppal.index');
    }
}
