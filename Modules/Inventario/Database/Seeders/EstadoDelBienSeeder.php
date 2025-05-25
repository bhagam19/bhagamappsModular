<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use SplFileObject;

class EstadoDelBienSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('estados')->insert([
            [
                'id' => 1,
                'nombre' => 'Nuevo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nombre' => 'Bueno',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nombre' => 'Regular',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'nombre' => 'Malo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
