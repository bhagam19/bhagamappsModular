<?php

namespace Modules\Apps\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Apps\Entities\App;

class AppSeeder extends Seeder
{
    public function run(): void
    {
        $apps = [
            ["Users", "usuarios/users", "users.png", 1],
            ["Inventario", "inventario/bienes", "inventario.png", 1],
            ["App", "app", "apps.png", 1],
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
            App::create([
                'nombre' => $nombre,
                'ruta' => '/' . $ruta,
                'imagen' => "vendor/adminlte/dist/img/Apps/{$imagen}",
                'user_id' => 1, 
                'habilitada' => $habilitada,
            ]);
        }
    }
}
