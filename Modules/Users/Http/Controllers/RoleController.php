<?php

namespace Modules\Users\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Users\Models\Role;
use Modules\Users\Models\Permission;

use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();    
        return view('users::roles.index',compact('roles'));
    }

}
