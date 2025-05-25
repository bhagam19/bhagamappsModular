<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use SplFileObject;

class MantenimientosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('mantenimientos')->insert([
            [
                'id' => 1,
                'nombre' => 'Al Día',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nombre' => 'En Mora',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nombre' => 'Dado de Baja',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
