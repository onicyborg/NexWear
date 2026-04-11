<?php

namespace App\Http\Controllers;

class QcController extends Controller
{
    public function index()
    {
        return response('QC Dashboard');
    }
}
