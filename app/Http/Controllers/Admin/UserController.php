<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;

class UserController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $users = User::all();    
        return view('admin.users.index',compact('users'));
    }  
    
}
