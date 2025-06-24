<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class HistorialModificacionesBienesSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        foreach (range(1, 15) as $i) {
            DB::table('historial_modificaciones_bienes')->insert([
                'bien_id' => $faker->numberBetween(1, 10),
                'tipo_objeto' => $faker->randomElement(['bien', 'detalle']),
                'campo_modificado' => $faker->randomElement(['descripcion', 'color', 'marca', 'serial']),
                'valor_anterior' => $faker->word(),
                'valor_nuevo' => $faker->word(),
                'user_id' => 1,       // Puedes randomizar si quieres
                'aprobado_por' => 1,     // Puedes randomizar si quieres
                'fecha_modificacion' => $faker->dateTimeBetween('-1 years', 'now'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
