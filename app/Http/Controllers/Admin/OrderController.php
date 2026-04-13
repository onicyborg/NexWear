<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\OrderItem;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['customer','orderItems'])->orderByDesc('created_at')->get();
        $customers = Customer::orderBy('name')->get();
        return view('admin.orders.index', compact('orders','customers'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $statuses = ['pending','cutting','sewing','qc','completed'];
        return view('admin.orders.create', compact('customers','statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required','exists:customers,id'],
            'export_date' => ['nullable','date'],
            'destination_country' => ['nullable','string','max:100'],
            'ship_mode' => ['nullable','string','max:100'],
        ]);

        // Generate unique order_no and po_number
        do {
            $orderNo = 'ORD-' . now()->format('ymd') . '-' . Str::upper(Str::random(5));
        } while (Order::where('order_no', $orderNo)->exists());

        do {
            $poNumber = 'PO-' . now()->format('ymd') . '-' . Str::upper(Str::random(5));
        } while (Order::where('po_number', $poNumber)->exists());

        // Validate items arrays (at least 1 valid row)
        $sizes = (array) $request->input('item_size', []);
        $qtys = (array) $request->input('item_quantity', []);
        $colorCodes = (array) $request->input('item_color_code', []);
        $colorNames = (array) $request->input('item_color_name', []);

        $items = [];
        foreach ($sizes as $i => $sz) {
            $sz = trim((string)$sz);
            $q = isset($qtys[$i]) ? (int)$qtys[$i] : 0;
            if ($sz !== '' && $q > 0) {
                $items[] = [
                    'size' => $sz,
                    'quantity' => $q,
                    'color_code' => isset($colorCodes[$i]) ? (string)$colorCodes[$i] : null,
                    'color_name' => isset($colorNames[$i]) ? (string)$colorNames[$i] : null,
                ];
            }
        }
        if (!count($items)) {
            return back()->withErrors(['item_size' => 'Minimal 1 baris item dengan ukuran dan quantity > 0 wajib diisi.'])->withInput();
        }

        $payload = $validated;
        $payload['order_no'] = $orderNo;
        $payload['po_number'] = $poNumber;
        $payload['status'] = 'pending';

        DB::transaction(function () use ($payload, $items) {
            $order = Order::create($payload);
            foreach ($items as $it) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'color_code' => $it['color_code'],
                    'color_name' => $it['color_name'],
                    'size' => $it['size'],
                    'quantity' => $it['quantity'],
                ]);
            }
        });

        return redirect()->route('orders.index')->with('success','Order berhasil dibuat');
    }

    public function show(string $id)
    {
        $order = Order::with([
            'customer',
            'orderItems',
            'productionTrackings' => function ($q) {
                $q->orderByDesc('created_at');
            },
            'productionTrackings.processedBy',
        ])->findOrFail($id);

        // Build stepper states from productionTrackings
        $steps = [
            ['key' => 'pending', 'label' => 'Pending'],
            ['key' => 'cutting', 'label' => 'Cutting'],
            ['key' => 'sewing', 'label' => 'Sewing'],
            ['key' => 'qc', 'label' => 'QC'],
            ['key' => 'completed', 'label' => 'Completed'],
        ];

        $lower = fn($s) => strtolower((string) $s);
        $findTrack = function (string $key) use ($order, $lower) {
            foreach ($order->productionTrackings as $t) {
                if (str_contains($lower($t->status), $key)) {
                    return $t;
                }
            }
            return null;
        };

        $stepsStatus = [];
        foreach ($steps as $step) {
            $trk = $findTrack($step['key']);
            $state = 'pending';
            if ($trk) {
                $state = $trk->completed_at ? 'done' : 'active';
            } elseif ($step['key'] === 'pending') {
                // If no tracking yet, initial state is active pending
                $state = count($order->productionTrackings) ? 'done' : 'active';
            }
            $stepsStatus[] = [
                'key' => $step['key'],
                'label' => $step['label'],
                'state' => $state,
                'track' => $trk,
            ];
        }

        return view('admin.orders.show', compact('order', 'stepsStatus'));
    }

    public function edit(Order $order)
    {
        $customers = Customer::orderBy('name')->get();
        $statuses = ['pending','cutting','sewing','qc','completed'];
        return view('admin.orders.edit', compact('order','customers','statuses'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'customer_id' => ['required','exists:customers,id'],
            'export_date' => ['nullable','date'],
            'destination_country' => ['nullable','string','max:100'],
            'ship_mode' => ['nullable','string','max:100'],
        ]);

        // Keep generated order_no and po_number immutable; do not update status here
        // Validate items arrays (at least 1 valid row)
        $sizes = (array) $request->input('item_size', []);
        $qtys = (array) $request->input('item_quantity', []);
        $colorCodes = (array) $request->input('item_color_code', []);
        $colorNames = (array) $request->input('item_color_name', []);

        $items = [];
        foreach ($sizes as $i => $sz) {
            $sz = trim((string)$sz);
            $q = isset($qtys[$i]) ? (int)$qtys[$i] : 0;
            if ($sz !== '' && $q > 0) {
                $items[] = [
                    'size' => $sz,
                    'quantity' => $q,
                    'color_code' => isset($colorCodes[$i]) ? (string)$colorCodes[$i] : null,
                    'color_name' => isset($colorNames[$i]) ? (string)$colorNames[$i] : null,
                ];
            }
        }
        if (!count($items)) {
            return back()->withErrors(['item_size' => 'Minimal 1 baris item dengan ukuran dan quantity > 0 wajib diisi.'])->withInput();
        }

        DB::transaction(function () use ($order, $validated, $items) {
            $order->update($validated);
            $order->orderItems()->delete();
            foreach ($items as $it) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'color_code' => $it['color_code'],
                    'color_name' => $it['color_name'],
                    'size' => $it['size'],
                    'quantity' => $it['quantity'],
                ]);
            }
        });

        return redirect()->route('orders.index')->with('success','Order berhasil diperbarui');
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('orders.index')->with('success','Order berhasil dihapus');
    }
}
