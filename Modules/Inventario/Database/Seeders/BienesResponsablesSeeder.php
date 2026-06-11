<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BienesResponsablesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 10) as $i) {
            DB::table('bienes_responsables')->insert([
                'bien_id' => $i,
                'user_id' => 1,
                'fecha_asignacion' => now()->subMonths(2)->toDateString(),
                'fecha_retiro' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
