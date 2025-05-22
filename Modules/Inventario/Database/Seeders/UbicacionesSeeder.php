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
                'nom_ubicacion' => 'Salón',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nom_ubicacion' => 'Oficina',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nom_ubicacion' => 'Departamento',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'nom_ubicacion' => 'Otro Lugar',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
