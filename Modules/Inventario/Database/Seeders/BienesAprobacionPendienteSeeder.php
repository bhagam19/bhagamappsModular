<?php

namespace Modules\Inventario\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Modules\Inventario\Entities\Bien;
use Modules\Inventario\Entities\BienAprobacionPendiente;
use Modules\Users\Models\User;

class BienesAprobacionPendienteSeeder extends Seeder
{
    public function run()
    {
         $faker = Faker::create();
        // Asegúrate de tener usuarios y bienes creados antes
        $usuario = User::whereNotIn('role_id', ['Administrador', 'Rector'])->inRandomOrder()->first();
        $bien = Bien::inRandomOrder()->first();

        if (!$usuario || !$bien) {
            $this->command->warn("No hay usuarios o bienes disponibles para poblar bienes_aprobacion_pendiente.");
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
                'usuario_id' => $usuario->id,
                'estado' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("Se insertaron cambios pendientes de aprobación para el bien ID {$bien->id}.");
    }
}
