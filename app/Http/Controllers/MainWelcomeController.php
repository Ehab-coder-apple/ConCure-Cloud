<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainWelcomeController extends Controller
{
    /**
     * Display the main welcome page that directs users to appropriate portals.
     */
    public function index()
    {
        return view('main-welcome');
    }
}
