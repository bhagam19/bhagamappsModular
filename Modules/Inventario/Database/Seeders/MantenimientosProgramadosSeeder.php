<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class MantenimientosProgramadosSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        foreach (range(1, 10) as $i) {
            DB::table('mantenimientos_programados')->insert([
                'bien_id' => $i,
                'usuario_id' => 1,
                'tipo' => $faker->randomElement(['preventivo', 'correctivo']),
                'titulo' => $faker->sentence(3),
                'descripcion' => $faker->paragraph(2),
                'fecha_programada' => Carbon::now()->addDays(rand(7, 60))->toDateString(),
                'fecha_realizada' => rand(0, 1) ? Carbon::now()->addDays(rand(61, 90))->toDateString() : null,
                'estado' => $faker->randomElement(['pendiente', 'realizado', 'cancelado']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
