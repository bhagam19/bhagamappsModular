<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;

class InventarioDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Orden recomendado por dependencias entre tablas
        $this->call([
            AlmacenamientosSeeder::class,
            CategoriasSeeder::class,
            EstadosSeeder::class,
            MantenimientosSeeder::class,
            UbicacionesSeeder::class,
            DependenciasSeeder::class,
            BienesSeeder::class,
            DetallesSeeder::class,
            HistorialModificacionesBienesSeeder::class,
            HistorialDependenciasBienesSeeder::class,
            HistorialEliminacionesBienesSeeder::class,
            BienesImagenesSeeder::class,
            MantenimientosProgramadosSeeder::class,


        ]);
    }
}
