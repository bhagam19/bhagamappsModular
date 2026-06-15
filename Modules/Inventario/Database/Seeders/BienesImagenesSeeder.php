<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class BienesImagenesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('bienes_imagenes')->delete();

        $faker = Faker::create();

        foreach (range(1, 10) as $i) {
            DB::table('bienes_imagenes')->insert([
                'bien_id' => $i,
                'ruta_imagen' => 'imagenes/' . $faker->uuid . '.jpg', // ✅ nombre corregido
                'descripcion' => $faker->sentence(4),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
