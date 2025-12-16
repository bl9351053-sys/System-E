<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ChooseRoleController extends Controller
{
    /**
     * Show the choose role page
     */
    public function index()
    {
       
    }

    /**
     * Root route: show role choice view when not authenticated, otherwise redirect to dashboard
     */
    public function home()
    {
        
    }

    /**
     * Redirect to chosen role area
     * @param Request $request
     * @param string $role
     */
    public function redirect(Request $request, $role)
    {
    }
}
