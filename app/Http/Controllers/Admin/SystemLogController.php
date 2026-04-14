<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;

class SystemLogController extends Controller
{
    public function index()
    {
        $logs = SystemLog::with('user')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();
        return view('admin.system_logs.index', compact('logs'));
    }
}
