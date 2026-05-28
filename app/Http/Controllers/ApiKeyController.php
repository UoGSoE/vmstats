<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function index(): View
    {
        return view('api_key.index');
    }
}
