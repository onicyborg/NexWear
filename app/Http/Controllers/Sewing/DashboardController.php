<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductionTracking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingOrders = Order::with(['customer', 'orderItems', 'qcSummary'])
            ->whereIn('status', ['complete_cutting', 'waiting_sewing', 'rework_sewing'])
            ->orderByDesc('created_at')
            ->get();

        $inProgressOrders = Order::with(['customer', 'orderItems', 'qcSummary'])
            ->where('status', 'on_process_sewing')
            ->orderByDesc('created_at')
            ->get();

        return view('sewing.dashboard', compact('pendingOrders', 'inProgressOrders'));
    }

    public function startProcess(Order $order): RedirectResponse
    {
        $order->update(['status' => 'on_process_sewing']);

        ProductionTracking::create([
            'order_id' => $order->id,
            'status' => 'sewing',
            'processed_by' => Auth::id(),
            'started_at' => now(),
        ]);

        return back()->with('success', 'Proses sewing dimulai untuk PO ' . ($order->po_number ?? $order->order_no));
    }

    public function completeProcess(Order $order): RedirectResponse
    {
        $order->update(['status' => 'waiting_qc']);

        $tracking = ProductionTracking::where('order_id', $order->id)
            ->where('status', 'sewing')
            ->whereNull('completed_at')
            ->latest()
            ->first();
        if ($tracking) {
            $tracking->update(['completed_at' => now()]);
        }

        return back()->with('success', 'Proses sewing selesai untuk PO ' . ($order->po_number ?? $order->order_no));
    }

    public function history()
    {
        $historyOrders = Order::with([
                'customer',
                'orderItems',
                'productionTrackings' => function ($q) {
                    $q->where('status', 'sewing')
                      ->whereNotNull('completed_at')
                      ->orderByDesc('started_at');
                }
            ])
            ->whereHas('productionTrackings', function ($q) {
                $q->where('status', 'sewing')->whereNotNull('completed_at');
            })
            ->orderByDesc('export_date')
            ->get();

        return view('sewing.history', compact('historyOrders'));
    }
}
