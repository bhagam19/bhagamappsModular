<?php

namespace Modules\User\Http\Controllers;

use Modules\User\Entities\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    { 
        $users = User::all();
        return view('user::user.index', compact('users'));
    }
}
