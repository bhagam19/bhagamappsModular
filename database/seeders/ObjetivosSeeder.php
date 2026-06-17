<?php

namespace Database\Seeders;

use App\Models\Objetivo;
use App\Models\Proceso;
use Illuminate\Database\Seeder;

class ObjetivosSeeder extends Seeder
{
    public function run(): void
    {
        $objetivos = [
            // Gestión Directiva
            ['proceso' => 'GD-01', 'codigo' => 'OBJ-GD-01', 'nombre' => 'Fortalecer el direccionamiento estratégico institucional mediante la consolidación de una identidad compartida que oriente las decisiones y acciones hacia el mejoramiento continuo.'],
            ['proceso' => 'GD-02', 'codigo' => 'OBJ-GD-02', 'nombre' => 'Consolidar una gestión estratégica efectiva mediante la articulación, seguimiento y evaluación permanente de los planes, proyectos y acciones institucionales.'],
            ['proceso' => 'GD-03', 'codigo' => 'OBJ-GD-03', 'nombre' => 'Promover la participación democrática de la comunidad educativa mediante el fortalecimiento de los órganos de gobierno escolar y los mecanismos de representación institucional.'],
            ['proceso' => 'GD-04', 'codigo' => 'OBJ-GD-04', 'nombre' => 'Fortalecer la cultura institucional mediante prácticas de comunicación, colaboración y reconocimiento que favorezcan el sentido de pertenencia y el mejoramiento continuo.'],
            ['proceso' => 'GD-05', 'codigo' => 'OBJ-GD-05', 'nombre' => 'Garantizar un clima escolar favorable mediante estrategias que promuevan la convivencia, el bienestar y la participación de la comunidad educativa.'],
            ['proceso' => 'GD-06', 'codigo' => 'OBJ-GD-06', 'nombre' => 'Fortalecer las relaciones institucionales con el entorno mediante alianzas estratégicas que contribuyan al desarrollo integral de los estudiantes y la comunidad.'],
            // Gestión Académica
            ['proceso' => 'GA-01', 'codigo' => 'OBJ-GA-01', 'nombre' => 'Fortalecer el diseño pedagógico institucional mediante la actualización permanente del currículo y las estrategias de aprendizaje orientadas al desarrollo integral de los estudiantes.'],
            ['proceso' => 'GA-02', 'codigo' => 'OBJ-GA-02', 'nombre' => 'Mejorar las prácticas pedagógicas mediante la implementación de estrategias didácticas innovadoras que favorezcan aprendizajes significativos y pertinentes.'],
            ['proceso' => 'GA-03', 'codigo' => 'OBJ-GA-03', 'nombre' => 'Fortalecer la gestión de aula mediante prácticas pedagógicas planificadas, inclusivas y centradas en el aprendizaje de los estudiantes.'],
            ['proceso' => 'GA-04', 'codigo' => 'OBJ-GA-04', 'nombre' => 'Fortalecer el seguimiento académico mediante mecanismos sistemáticos de evaluación, acompañamiento y mejora continua del desempeño estudiantil.'],
            // Gestión Administrativa y Financiera
            ['proceso' => 'GAF-01', 'codigo' => 'OBJ-GAF-01', 'nombre' => 'Garantizar el apoyo eficiente a la gestión académica mediante procesos administrativos oportunos que aseguren la disponibilidad y confiabilidad de la información institucional.'],
            ['proceso' => 'GAF-02', 'codigo' => 'OBJ-GAF-02', 'nombre' => 'Asegurar la disponibilidad, conservación y uso adecuado de la infraestructura y los recursos institucionales que respaldan los procesos educativos.'],
            ['proceso' => 'GAF-03', 'codigo' => 'OBJ-GAF-03', 'nombre' => 'Garantizar la prestación oportuna y de calidad de los servicios complementarios que contribuyen al bienestar y permanencia de los estudiantes.'],
            ['proceso' => 'GAF-04', 'codigo' => 'OBJ-GAF-04', 'nombre' => 'Fortalecer la gestión del talento humano mediante estrategias de selección, formación, acompañamiento y evaluación que favorezcan el desempeño institucional.'],
            ['proceso' => 'GAF-05', 'codigo' => 'OBJ-GAF-05', 'nombre' => 'Garantizar la sostenibilidad financiera institucional mediante una gestión transparente, eficiente y responsable de los recursos económicos.'],
            // Gestión de la Comunidad
            ['proceso' => 'GC-01', 'codigo' => 'OBJ-GC-01', 'nombre' => 'Garantizar el acceso, permanencia e inclusión de todos los estudiantes mediante estrategias institucionales que favorezcan la equidad educativa.'],
            ['proceso' => 'GC-02', 'codigo' => 'OBJ-GC-02', 'nombre' => 'Fortalecer la proyección institucional hacia la comunidad mediante programas, servicios y espacios de participación que generen valor social.'],
            ['proceso' => 'GC-03', 'codigo' => 'OBJ-GC-03', 'nombre' => 'Promover la participación activa y la convivencia armónica mediante estrategias que fortalezcan los vínculos entre los diferentes actores de la comunidad educativa.'],
            ['proceso' => 'GC-04', 'codigo' => 'OBJ-GC-04', 'nombre' => 'Fortalecer la prevención y gestión de riesgos mediante acciones institucionales que protejan la integridad física, emocional y social de la comunidad educativa.'],
        ];

        $procesos = Proceso::pluck('id', 'codigo');

        foreach ($objetivos as $data) {
            Objetivo::firstOrCreate(
                ['codigo' => $data['codigo']],
                [
                    'proceso_id'  => $procesos[$data['proceso']],
                    'nombre'      => $data['nombre'],
                    'activo'      => true,
                ]
            );
        }
    }
}
