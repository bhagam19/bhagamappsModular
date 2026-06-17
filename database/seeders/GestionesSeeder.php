<?php

namespace Database\Seeders;

use App\Models\Gestion;
use Illuminate\Database\Seeder;

class GestionesSeeder extends Seeder
{
    public function run(): void
    {
        $gestiones = [
            ['codigo' => 'GD',  'nombre' => 'Gestión Directiva',                    'descripcion' => 'Responsable del direccionamiento estratégico institucional.', 'orden' => 1],
            ['codigo' => 'GA',  'nombre' => 'Gestión Académica',                    'descripcion' => 'Responsable de los procesos pedagógicos y formativos.',       'orden' => 2],
            ['codigo' => 'GAF', 'nombre' => 'Gestión Administrativa y Financiera',  'descripcion' => 'Responsable del soporte institucional.',                       'orden' => 3],
            ['codigo' => 'GC',  'nombre' => 'Gestión de la Comunidad',              'descripcion' => 'Responsable de la relación entre la institución y su comunidad.', 'orden' => 4],
        ];

        foreach ($gestiones as $data) {
            Gestion::firstOrCreate(['codigo' => $data['codigo']], array_merge($data, ['activo' => true]));
        }
    }
}
