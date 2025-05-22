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
                'nom_mantenimiento' => 'Al Día',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nom_mantenimiento' => 'En Mora',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nom_mantenimiento' => 'Dado de Baja',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
