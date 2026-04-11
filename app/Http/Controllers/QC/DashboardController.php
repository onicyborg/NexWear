<?php

namespace App\Http\Controllers\QC;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('qc.dashboard');
    }
}
