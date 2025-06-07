<?php

namespace Modules\Apps\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Apps\Entities\Aplicacion;

class AppSeeder extends Seeder
{
    public function run(): void
    {
        $apps = [
            ["Inventario", "inventario", "inventario.png", 1],
            ["Biblioteca", "biblioteca", "biblioteca.png", 1],
            ["SINAI vs SIMAT", "SvS", "SvS.png", 0],
            ["Planeador", "planeador", "planeador.png", 0],
            ["EduInclusiva", "eduInclusiva", "DUA.png", 0],
            ["CTE", "cte", "cteApp.jpg", 0],
            ["Creador de Exámenes", "creadorExamenes", "examCreator.jpg", 0],
            ["Tabletas", "prestamoTabletas", "tablet.jpg", 0],
            ["Polla Mundialista", "pollaMundialista", "pollaMundialista.png", 0],
            ["Evaluar para Avanzar", "evaluarParaAvanzar", "EvPAv.png", 0],
        ];

        foreach ($apps as [$nombre, $ruta, $imagen, $habilitada]) {
            Aplicacion::create([
                'nombre' => $nombre,
                'ruta' => '/' . $ruta,
                'imagen' => "vendor/adminlte/dist/img/Apps/{$imagen}",
                'usuario_id' => 1, // Ajusta si necesitas relacionarlo dinámicamente
                'habilitada' => $habilitada,
            ]);
        }
    }
}
