<?php

namespace Database\Seeders;

use App\Models\Gestion;
use App\Models\Proceso;
use Illuminate\Database\Seeder;

class ProcesosSeeder extends Seeder
{
    public function run(): void
    {
        $procesos = [
            // GD — Gestión Directiva
            ['gestion' => 'GD', 'codigo' => 'GD-01', 'nombre' => 'Direccionamiento Estratégico y Horizonte Institucional', 'orden' => 1],
            ['gestion' => 'GD', 'codigo' => 'GD-02', 'nombre' => 'Gestión Estratégica',                                    'orden' => 2],
            ['gestion' => 'GD', 'codigo' => 'GD-03', 'nombre' => 'Gobierno Escolar',                                       'orden' => 3],
            ['gestion' => 'GD', 'codigo' => 'GD-04', 'nombre' => 'Cultura Institucional',                                  'orden' => 4],
            ['gestion' => 'GD', 'codigo' => 'GD-05', 'nombre' => 'Clima Escolar',                                          'orden' => 5],
            ['gestion' => 'GD', 'codigo' => 'GD-06', 'nombre' => 'Relaciones con el Entorno',                              'orden' => 6],
            // GA — Gestión Académica
            ['gestion' => 'GA', 'codigo' => 'GA-01', 'nombre' => 'Diseño Pedagógico',                                      'orden' => 1],
            ['gestion' => 'GA', 'codigo' => 'GA-02', 'nombre' => 'Prácticas Pedagógicas',                                  'orden' => 2],
            ['gestion' => 'GA', 'codigo' => 'GA-03', 'nombre' => 'Gestión de Aula',                                        'orden' => 3],
            ['gestion' => 'GA', 'codigo' => 'GA-04', 'nombre' => 'Seguimiento Académico',                                  'orden' => 4],
            // GAF — Gestión Administrativa y Financiera
            ['gestion' => 'GAF', 'codigo' => 'GAF-01', 'nombre' => 'Apoyo a la Gestión Académica',                         'orden' => 1],
            ['gestion' => 'GAF', 'codigo' => 'GAF-02', 'nombre' => 'Administración de Planta Física y Recursos',           'orden' => 2],
            ['gestion' => 'GAF', 'codigo' => 'GAF-03', 'nombre' => 'Administración de Servicios Complementarios',          'orden' => 3],
            ['gestion' => 'GAF', 'codigo' => 'GAF-04', 'nombre' => 'Talento Humano',                                       'orden' => 4],
            ['gestion' => 'GAF', 'codigo' => 'GAF-05', 'nombre' => 'Apoyo Financiero y Contable',                          'orden' => 5],
            // GC — Gestión de la Comunidad
            ['gestion' => 'GC', 'codigo' => 'GC-01', 'nombre' => 'Accesibilidad',                                          'orden' => 1],
            ['gestion' => 'GC', 'codigo' => 'GC-02', 'nombre' => 'Proyección a la Comunidad',                              'orden' => 2],
            ['gestion' => 'GC', 'codigo' => 'GC-03', 'nombre' => 'Participación y Convivencia',                            'orden' => 3],
            ['gestion' => 'GC', 'codigo' => 'GC-04', 'nombre' => 'Prevención de Riesgos',                                  'orden' => 4],
        ];

        $gestiones = Gestion::pluck('id', 'codigo');

        foreach ($procesos as $data) {
            $gestionId = $gestiones[$data['gestion']];
            Proceso::firstOrCreate(
                ['codigo' => $data['codigo']],
                ['gestion_id' => $gestionId, 'nombre' => $data['nombre'], 'orden' => $data['orden'], 'activo' => true]
            );
        }
    }
}
