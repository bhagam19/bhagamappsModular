<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class HistorialEliminacionesBienesSeeder extends Seeder
{
    /*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Seed the historial_dependencias_bienes table with dummy data.
     *
     * This method generates random data for 10 entries in the
     * historial_dependencias_bienes table, including fields such as
     * bien_id, dependencia_anterior_id, dependencia_nueva_id,
     * usuario_id, aprobado_por, and fecha_modificacion.
     * It uses the Faker library to create realistic random data for testing
     * purposes.
     */


    /*******  0f5ca49b-bdf8-4ffa-939f-dd300a05b903  *******/
    public function run(): void
    {
        $faker = Faker::create();

        foreach (range(1, 10) as $i) {
            DB::table('historial_eliminaciones')->insert([
                ['bien_id' => 1, 'usuario_id' => 1, 'estado' => 'pendiente', 'motivo' => 'Dañado irreparable', 'created_at' => now(), 'updated_at' => now()],
                ['bien_id' => 1, 'usuario_id' => 1, 'estado' => 'pendiente', 'motivo' => 'Obsoleto', 'created_at' => now(), 'updated_at' => now()],
                ['bien_id' => 1, 'usuario_id' => 1, 'estado' => 'pendiente', 'motivo' => 'Extraviado', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}
