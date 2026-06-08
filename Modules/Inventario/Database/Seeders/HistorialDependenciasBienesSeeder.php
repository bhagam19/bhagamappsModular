<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class HistorialDependenciasBienesSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        foreach (range(1, 10) as $i) {
            DB::table('historial_dependencias_bienes')->insert([
                'bien_id' => $i,
                'dependencia_anterior_id' => rand(1, 4), // suponiendo que tienes 4 dependencias
                'dependencia_nueva_id' => rand(1, 4),
                'user_id' => 1, // quien hizo el cambio
                'aprobado_por' => 1, // quien aprobó el cambio
                'fecha_modificacion' => Carbon::now()->subDays(rand(1, 60)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
