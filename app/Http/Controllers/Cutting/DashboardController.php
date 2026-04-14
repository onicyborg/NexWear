<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductionTracking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $orders = Order::with(['customer', 'orderItems'])
            ->orderByDesc('created_at')
            ->get();

        $pendingOrders = $orders->where('status', 'pending');
        $inProgressOrders = $orders->where('status', 'on_process_cutting');

        return view('cutting.dashboard', compact('pendingOrders', 'inProgressOrders'));
    }

    public function startProcess(Order $order): RedirectResponse
    {
        $order->update(['status' => 'on_process_cutting']);

        ProductionTracking::create([
            'order_id' => $order->id,
            'status' => 'cutting',
            'processed_by' => Auth::id(),
            'started_at' => now(),
        ]);

        return back()->with('success', 'Proses cutting dimulai untuk PO ' . ($order->po_number ?? $order->order_no));
    }

    public function completeProcess(Order $order): RedirectResponse
    {
        $order->update(['status' => 'complete_cutting']);

        $tracking = ProductionTracking::where('order_id', $order->id)
            ->where('status', 'cutting')
            ->whereNull('completed_at')
            ->latest()
            ->first();
        if ($tracking) {
            $tracking->update(['completed_at' => now()]);
        }

        return back()->with('success', 'Proses cutting selesai untuk PO ' . ($order->po_number ?? $order->order_no));
    }
}
