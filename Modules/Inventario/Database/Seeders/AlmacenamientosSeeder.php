<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlmacenamientosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('almacenamientos')->insert([
            [
                'id' => 1,
                'nom_almacenamiento' => 'En Uso',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nom_almacenamiento' => 'Almacenado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
