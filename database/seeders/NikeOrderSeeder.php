<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;

class NikeOrderSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure customer NIKE exists
        $customer = Customer::where('name', 'NIKE')->first();
        if (!$customer) {
            $code = 'NIKE';
            if (Customer::where('customer_code', $code)->exists()) {
                $code = 'NIKE-' . Str::upper(Str::random(4));
            }
            $customer = Customer::create([
                'customer_code' => $code,
                'name' => 'NIKE',
                'email' => null,
                'phone' => null,
                'address' => 'Beaverton, Oregon, USA',
                'is_active' => true,
            ]);
        }

        // Create a single PO/Order
        do {
            $orderNo = 'ORD-SEED-' . Str::upper(Str::random(6));
        } while (Order::where('order_no', $orderNo)->exists());

        do {
            $poNumber = 'PO-SEED-' . Str::upper(Str::random(6));
        } while (Order::where('po_number', $poNumber)->exists());

        $order = Order::create([
            'order_no' => $orderNo,
            'po_number' => $poNumber,
            'customer_id' => $customer->id,
            'export_date' => Carbon::now()->addDays(30)->toDateString(),
            'destination_country' => 'USA',
            'ship_mode' => 'SEA',
            'status' => 'pending',
        ]);

        // Dataset: color rows with per-size quantities
        $rows = [
            ['010', 'BLACK', 42, 179, 141, 37, 81],
            ['012', 'WL FGRY/BLACK', 77, 83, 29, 66, 0],
            ['463', 'RYL BL/MNNAVY', 122, 318, 475, 384, 376],
            ['410', 'MNNAVY/RYL BL', 11, 216, 399, 533, 555],
            ['657', 'UNVRED/BLACK', 107, 112, 374, 332, 513],
            ['719', 'TOUR Y/BLACK', 43, 94, 91, 87, 87],
        ];
        $sizes = ['XS','S','M','L','XL'];

        foreach ($rows as $row) {
            [$code, $name, $xs, $s, $m, $l, $xl] = $row;
            $qtys = [$xs, $s, $m, $l, $xl];
            foreach ($sizes as $i => $sz) {
                $q = (int) $qtys[$i];
                if ($q < 0) $q = 0;
                // Create item even if quantity 0? We'll skip zero quantities to keep dataset clean
                if ($q === 0) continue;
                OrderItem::create([
                    'order_id' => $order->id,
                    'color_code' => $code,
                    'color_name' => $name,
                    'size' => $sz,
                    'quantity' => $q,
                ]);
            }
        }
    }
}
