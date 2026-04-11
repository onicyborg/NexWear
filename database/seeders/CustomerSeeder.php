<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::query()->create([
            'customer_code' => 'NKE',
            'name' => 'NIKE',
            'email' => null,
            'phone' => null,
            'address' => null,
            'is_active' => true,
        ]);

        Customer::query()->create([
            'customer_code' => 'ADS',
            'name' => 'ADIDAS',
            'email' => null,
            'phone' => null,
            'address' => null,
            'is_active' => true,
        ]);
    }
}
