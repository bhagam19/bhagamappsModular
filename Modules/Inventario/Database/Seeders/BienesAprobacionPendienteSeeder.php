<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Modules\Inventario\Entities\Bien;
use Modules\Inventario\Entities\BienAprobacionPendiente;

class BienesAprobacionPendienteSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Obtener un bien con dependencia asociada
        $bien = Bien::whereNotNull('dependencia_id')->inRandomOrder()->first();

        if (!$bien) {
            $this->command->warn("No hay bienes con dependencia disponible para poblar bienes_aprobacion_pendiente.");
            return;
        }

        $cambios = [
            [
                'campo' => 'nombre',
                'valor_anterior' => $bien->nombre,
                'valor_nuevo' => $bien->nombre . ' Modificado',
            ],
            [
                'campo' => 'precio',
                'valor_anterior' => $bien->precio,
                'valor_nuevo' => $bien->precio + 100,
            ],
            [
                'campo' => 'cantidad',
                'valor_anterior' => $bien->cantidad,
                'valor_nuevo' => $bien->cantidad + 1,
            ],
        ];

        foreach ($cambios as $cambio) {
            BienAprobacionPendiente::create([
                'bien_id' => $bien->id,
                'tipo_objeto' => 'bien',
                'campo' => $cambio['campo'],
                'valor_anterior' => $cambio['valor_anterior'],
                'valor_nuevo' => $cambio['valor_nuevo'],
                'dependencia_id' => $bien->dependencia_id, // Asignar dependencia del bien
                'estado' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("Se insertaron cambios pendientes de aprobación para el bien ID {$bien->id}.");
    }
}
