<?php

namespace App\Http\Controllers;

class MessagesPageController extends Controller
{
    public function index()
    {
        return view('messages.index');
    }
}

