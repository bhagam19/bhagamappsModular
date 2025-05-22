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
        DB::table('estado_del_bien')->insert([
            [
                'id' => 1,
                'nom_estado' => 'Nuevo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nom_estado' => 'Bueno',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nom_estado' => 'Regular',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'nom_estado' => 'Malo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
