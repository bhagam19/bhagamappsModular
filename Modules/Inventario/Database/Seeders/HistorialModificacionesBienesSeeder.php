<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class HistorialModificacionesBienesSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        foreach (range(1, 15) as $i) {
            DB::table('historial_modificaciones_bienes')->insert([
                'bien_id' => $faker->numberBetween(1, 10),
                'usuario_id' => 1,
                'cambios' => json_encode(['detalle' => 'actualización de datos']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
