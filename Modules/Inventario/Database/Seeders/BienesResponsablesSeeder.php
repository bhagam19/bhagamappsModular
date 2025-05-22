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
                'usuario_id' => 1,
                'fecha_inicio' => now()->subMonths(2),
                'fecha_fin' => now(), // o null si aún es responsable
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
