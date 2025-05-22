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
                'nom_bien' => $faker->word,
                'detalle_del_bien' => $faker->sentence,
                'serie_del_bien' => $faker->uuid,
                'origen_del_bien' => 'Compra',
                'fecha_adquisicion' => $faker->date(),
                'precio' => $faker->randomFloat(2, 100, 1000),
                'cant_bien' => $faker->numberBetween(1, 10),
                'cod_categoria' => 1,
                'cod_dependencias' => 1,
                'usuario_id' => 1,
                'cod_almacenamiento' => 1,
                'cod_estado' => 1,
                'cod_mantenimiento' => 1,
                'observaciones' => $faker->sentence,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
