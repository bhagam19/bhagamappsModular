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
            CategoriasDeBienesSeeder::class,
            EstadoDelBienSeeder::class,
            MantenimientosSeeder::class,
            UbicacionesSeeder::class,
            DependenciasSeeder::class,
            BienesSeeder::class,
            DetallesDeBienesSeeder::class,
            BienesAprobacionPendienteSeeder::class,
            HistorialModificacionesBienesSeeder::class,
            HistorialUbicacionesBienesSeeder::class,
            BienesResponsablesSeeder::class,
            BienesImagenesSeeder::class,
            MantenimientosProgramadosSeeder::class,
            
        ]);
    }
}
