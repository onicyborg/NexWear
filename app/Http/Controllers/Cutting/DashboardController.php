<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('cutting.dashboard');
    }
}
