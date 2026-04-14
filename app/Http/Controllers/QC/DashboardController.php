<?php

namespace App\Http\Controllers\QC;

use App\Http\Controllers\Controller;
use App\Models\MasterQcKpi;
use App\Models\Order;
use App\Models\OrderQcChecklist;
use App\Models\OrderQcSummary;
use App\Models\ProductionTracking;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();

        $pendingCount = Order::whereIn('status', ['waiting_qc', 'on_process_qc'])->count();
        $completedToday = OrderQcSummary::whereDate('updated_at', $today)->count();
        $totalReworkToday = (int) OrderQcSummary::whereDate('updated_at', $today)->sum('qty_rework');
        $totalRejectToday = (int) OrderQcSummary::whereDate('updated_at', $today)->sum('qty_reject');

        $recentOrders = Order::query()
            ->select('orders.*')
            ->join('order_qc_summaries as oqs', 'oqs.order_id', '=', 'orders.id')
            ->with(['customer', 'qcSummary'])
            ->orderByDesc('oqs.updated_at')
            ->limit(5)
            ->get();

        return view('qc.dashboard', compact(
            'pendingCount',
            'completedToday',
            'totalReworkToday',
            'totalRejectToday',
            'recentOrders'
        ));
    }

    public function index()
    {
        $orders = Order::with(['customer'])
            ->whereIn('status', ['waiting_qc', 'on_process_qc'])
            ->orderBy('export_date')
            ->get();

        return view('qc.index', compact('orders'));
    }

    public function history()
    {
        $orders = Order::query()
            ->select('orders.*')
            ->join('order_qc_summaries as oqs', 'oqs.order_id', '=', 'orders.id')
            ->with(['customer', 'qcSummary'])
            ->orderByDesc('oqs.updated_at')
            ->get();

        $orders->load(['orderQcChecklists']);

        return view('qc.history', compact('orders'));
    }

    public function inspect(Order $order)
    {
        if ($order->status !== 'on_process_qc') {
            $order->update(['status' => 'on_process_qc']);
        }

        // Start a QC tracking entry (avoid duplicates when page is reloaded)
        $openTracking = ProductionTracking::where('order_id', $order->id)
            ->where('status', 'qc')
            ->whereNull('completed_at')
            ->latest()
            ->first();
        if (!$openTracking) {
            ProductionTracking::create([
                'order_id' => $order->id,
                'status' => 'qc',
                'processed_by' => Auth::id(),
                'started_at' => now(),
            ]);
        }

        $order->load(['customer', 'orderItems', 'qcSummary', 'orderQcChecklists']);
        $totalQty = (int) $order->orderItems->sum('quantity');
        $masterKpis = MasterQcKpi::where('is_active', true)->orderBy('category')->get();

        return view('qc.inspect', compact('order', 'masterKpis', 'totalQty'));
    }

    public function submit(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'qty_pass' => ['required','integer','min:0'],
            'qty_rework' => ['required','integer','min:0'],
            'qty_reject' => ['required','integer','min:0'],
            'general_notes' => ['nullable','string'],
            'defect_kpis' => ['array'],
        ]);

        $targetQty = (int) $order->orderItems()->sum('quantity');
        $sum = (int)$validated['qty_pass'] + (int)$validated['qty_rework'] + (int)$validated['qty_reject'];
        if ($sum !== $targetQty) {
            return back()
                ->withErrors(['qty_pass' => 'Total QC (pass+rework+reject) harus sama dengan target qty inspeksi: ' . $targetQty])
                ->withInput();
        }

        // Upsert summary (one-to-one per order)
        OrderQcSummary::updateOrCreate(
            ['order_id' => $order->id],
            [
                'qty_pass' => (int)$validated['qty_pass'],
                'qty_rework' => (int)$validated['qty_rework'],
                'qty_reject' => (int)$validated['qty_reject'],
                'general_notes' => $validated['general_notes'] ?? null,
            ]
        );

        // Save defect checklists if provided (snapshot)
        $ids = (array)($request->input('defect_kpis', []));
        // Reset previous checklists for this order
        OrderQcChecklist::where('order_id', $order->id)->delete();
        if (!empty($ids)) {
            $kpis = MasterQcKpi::whereIn('id', $ids)->get();
            foreach ($kpis as $kpi) {
                OrderQcChecklist::create([
                    'order_id' => $order->id,
                    'qc_category' => $kpi->category,
                    'qc_instruction' => $kpi->instruction,
                    'is_passed' => false,
                    'notes' => null,
                    'checked_by' => Auth::id(),
                    'checked_at' => now(),
                ]);
            }
        }

        // Close current QC tracking & update order status
        $tracking = ProductionTracking::where('order_id', $order->id)
            ->where('status', 'qc')
            ->whereNull('completed_at')
            ->latest()
            ->first();

        if ((int)$validated['qty_rework'] > 0) {
            $order->update(['status' => 'rework_sewing']);
            if ($tracking) {
                $tracking->update(['completed_at' => now(), 'notes' => 'Returned to Sewing for rework']);
            } else {
                ProductionTracking::create([
                    'order_id' => $order->id,
                    'status' => 'qc',
                    'processed_by' => Auth::id(),
                    'notes' => 'Returned to Sewing for rework',
                    'completed_at' => now(),
                ]);
            }
        } else {
            $order->update(['status' => 'completed']);
            if ($tracking) {
                $tracking->update(['completed_at' => now(), 'notes' => 'QC passed']);
            } else {
                ProductionTracking::create([
                    'order_id' => $order->id,
                    'status' => 'qc',
                    'processed_by' => Auth::id(),
                    'notes' => 'QC passed',
                    'completed_at' => now(),
                ]);
            }
        }

        return redirect()->route('qc.queue')->with('success', 'Hasil QC berhasil disimpan.');
    }
}
