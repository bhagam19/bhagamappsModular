<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class HistorialUbicacionesBienesSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();    

        foreach (range(1, 10) as $i) {
            DB::table('historial_ubicaciones_bienes')->insert([
                'bien_id' => $i,
                'ubicacion_anterior_id' => rand(1, 4), // suponiendo que tienes 4 ubicaciones
                'ubicacion_nueva_id' => rand(1, 4),
                'usuario_id' => 1,
                'fecha_cambio' => Carbon::now()->subDays(rand(1, 60)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
