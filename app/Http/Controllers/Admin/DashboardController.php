<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\ProductionTracking;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $totalActivePo = Order::where('status', '!=', 'completed')->count();
        $deadlineSoonPo = Order::whereNotNull('export_date')
            ->whereDate('export_date', '>=', $today)
            ->whereDate('export_date', '<=', $today->copy()->addDays(3))
            ->where('status', '!=', 'completed')
            ->count();

        $itemsDoneToday = ProductionTracking::where('status', 'completed')
            ->whereDate('completed_at', $today)
            ->count();

        $totalCustomers = Customer::count();

        $statuses = Order::pluck('status');
        $dist = [
            'cutting' => 0,
            'sewing' => 0,
            'qc' => 0,
            'completed' => 0,
        ];
        foreach ($statuses as $s) {
            $ls = strtolower((string) $s);
            if ($ls === 'completed') { $dist['completed']++; continue; }
            if (str_contains($ls, 'qc')) { $dist['qc']++; continue; }
            if (str_contains($ls, 'sewing')) { $dist['sewing']++; continue; }
            if (str_contains($ls, 'cutting')) { $dist['cutting']++; continue; }
        }

        $urgentOrders = Order::with('customer')
            ->whereNotNull('export_date')
            ->whereDate('export_date', '>=', $today)
            ->where('status', '!=', 'completed')
            ->orderBy('export_date')
            ->limit(5)
            ->get()
            ->map(function ($o) {
                $ls = strtolower((string) $o->status);
                $p = 0;
                if ($ls === 'completed') $p = 100;
                elseif (str_contains($ls, 'qc')) $p = 75;
                elseif (str_contains($ls, 'sewing')) $p = 50;
                elseif (str_contains($ls, 'cutting')) $p = 25;
                else $p = 0;
                $o->setAttribute('progress_percent', $p);
                return $o;
            });

        return view('admin.dashboard', [
            'stats' => [
                'totalActivePo' => $totalActivePo,
                'deadlineSoonPo' => $deadlineSoonPo,
                'itemsDoneToday' => $itemsDoneToday,
                'totalCustomers' => $totalCustomers,
            ],
            'chart' => [
                'labels' => ['Cutting', 'Sewing', 'QC', 'Completed'],
                'series' => [
                    $dist['cutting'],
                    $dist['sewing'],
                    $dist['qc'],
                    $dist['completed'],
                ],
            ],
            'urgentOrders' => $urgentOrders,
        ]);
    }
}
