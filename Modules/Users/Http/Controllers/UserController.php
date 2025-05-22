<?php

namespace Modules\Users\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Users\Models\User;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $users = User::all();    
        return view('users::users.index', compact('users'));

    }  
    
}
