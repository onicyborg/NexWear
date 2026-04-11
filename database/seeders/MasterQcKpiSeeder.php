<?php

namespace Database\Seeders;

use App\Models\MasterQcKpi;
use Illuminate\Database\Seeder;

class MasterQcKpiSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['category' => 'Bagian Dalam', 'instruction' => 'Jahitan lurus tidak loncat', 'is_active' => true],
            ['category' => 'Bagian Luar', 'instruction' => 'Ukuran label sesuai dengan pesanan', 'is_active' => true],
            ['category' => 'Bagian Luar', 'instruction' => 'Tidak ada noda di kain', 'is_active' => true],
            ['category' => 'Umum', 'instruction' => 'Kerapihan finishing sesuai standar', 'is_active' => true],
        ];

        foreach ($items as $it) {
            MasterQcKpi::updateOrCreate(
                ['category' => $it['category'], 'instruction' => $it['instruction']],
                ['is_active' => $it['is_active']]
            );
        }
    }
}
