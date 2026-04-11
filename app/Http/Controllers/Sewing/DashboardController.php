<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('sewing.dashboard');
    }
}
