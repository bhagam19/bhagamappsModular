<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UbicacionesSeeder extends Seeder
{
    public function run(): void
    {       
        
        DB::table('ubicaciones')->insert([
            [
                'id' => 1,
                'nombre' => 'Salón',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nombre' => 'Oficina',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nombre' => 'Departamento',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'nombre' => 'Otro Lugar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
