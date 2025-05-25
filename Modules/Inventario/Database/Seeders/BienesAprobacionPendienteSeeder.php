<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class BienesAprobacionPendienteSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        foreach (range(1, 10) as $i) {
            DB::table('bienes_aprobacion_pendiente')->insert([
                'nombre' => $faker->word,
                'detalle' => $faker->sentence,
                'serie' => $faker->uuid,
                'origen' => 'Compra',
                'fechaAdquisicion' => $faker->date(),
                'precio' => $faker->randomFloat(2, 100, 1000),
                'cantidad' => $faker->numberBetween(1, 10),
                'categoria_id' => 1,
                'dependencia_id' => 1,
                'usuario_id' => 1,
                'almacenamiento_id' => 1,
                'estado_id' => 1,
                'mantenimiento_id' => 1,
                'observaciones' => $faker->sentence,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
