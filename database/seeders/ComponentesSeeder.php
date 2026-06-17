<?php

namespace Database\Seeders;

use App\Models\Componente;
use App\Models\Proceso;
use Illuminate\Database\Seeder;

class ComponentesSeeder extends Seeder
{
    public function run(): void
    {
        $componentes = [
            // GD-01
            ['proceso' => 'GD-01', 'codigo' => 'GD-01-01', 'nombre' => 'Misión, Visión y Principios',                        'orden' => 1],
            ['proceso' => 'GD-01', 'codigo' => 'GD-01-02', 'nombre' => 'Metas Institucionales',                               'orden' => 2],
            ['proceso' => 'GD-01', 'codigo' => 'GD-01-03', 'nombre' => 'Conocimiento y Apropiación del Direccionamiento',     'orden' => 3],
            ['proceso' => 'GD-01', 'codigo' => 'GD-01-04', 'nombre' => 'Política de Inclusión',                               'orden' => 4],
            // GD-02
            ['proceso' => 'GD-02', 'codigo' => 'GD-02-01', 'nombre' => 'Liderazgo',                                           'orden' => 1],
            ['proceso' => 'GD-02', 'codigo' => 'GD-02-02', 'nombre' => 'Articulación de Planes, Proyectos y Acciones',        'orden' => 2],
            ['proceso' => 'GD-02', 'codigo' => 'GD-02-03', 'nombre' => 'Estrategia Pedagógica',                               'orden' => 3],
            ['proceso' => 'GD-02', 'codigo' => 'GD-02-04', 'nombre' => 'Uso de Información',                                  'orden' => 4],
            ['proceso' => 'GD-02', 'codigo' => 'GD-02-05', 'nombre' => 'Seguimiento y Autoevaluación',                        'orden' => 5],
            // GD-03
            ['proceso' => 'GD-03', 'codigo' => 'GD-03-01', 'nombre' => 'Consejo Directivo',                                   'orden' => 1],
            ['proceso' => 'GD-03', 'codigo' => 'GD-03-02', 'nombre' => 'Consejo Académico',                                   'orden' => 2],
            ['proceso' => 'GD-03', 'codigo' => 'GD-03-03', 'nombre' => 'Comisión de Evaluación y Promoción',                  'orden' => 3],
            ['proceso' => 'GD-03', 'codigo' => 'GD-03-04', 'nombre' => 'Comité de Convivencia',                               'orden' => 4],
            ['proceso' => 'GD-03', 'codigo' => 'GD-03-05', 'nombre' => 'Personería Estudiantil',                              'orden' => 5],
            ['proceso' => 'GD-03', 'codigo' => 'GD-03-06', 'nombre' => 'Consejo Estudiantil',                                 'orden' => 6],
            ['proceso' => 'GD-03', 'codigo' => 'GD-03-07', 'nombre' => 'Asamblea y Consejo de Padres',                        'orden' => 7],
            // GD-04
            ['proceso' => 'GD-04', 'codigo' => 'GD-04-01', 'nombre' => 'Mecanismos de Comunicación',                         'orden' => 1],
            ['proceso' => 'GD-04', 'codigo' => 'GD-04-02', 'nombre' => 'Trabajo en Equipo',                                   'orden' => 2],
            ['proceso' => 'GD-04', 'codigo' => 'GD-04-03', 'nombre' => 'Reconocimiento de Logros',                            'orden' => 3],
            ['proceso' => 'GD-04', 'codigo' => 'GD-04-04', 'nombre' => 'Identificación y Divulgación de Buenas Prácticas',    'orden' => 4],
            // GD-05
            ['proceso' => 'GD-05', 'codigo' => 'GD-05-01', 'nombre' => 'Pertenencia y Participación',                        'orden' => 1],
            ['proceso' => 'GD-05', 'codigo' => 'GD-05-02', 'nombre' => 'Ambiente Físico',                                     'orden' => 2],
            ['proceso' => 'GD-05', 'codigo' => 'GD-05-03', 'nombre' => 'Inducción',                                           'orden' => 3],
            ['proceso' => 'GD-05', 'codigo' => 'GD-05-04', 'nombre' => 'Motivación',                                          'orden' => 4],
            ['proceso' => 'GD-05', 'codigo' => 'GD-05-05', 'nombre' => 'Manual de Convivencia',                               'orden' => 5],
            ['proceso' => 'GD-05', 'codigo' => 'GD-05-06', 'nombre' => 'Actividades Extracurriculares',                       'orden' => 6],
            ['proceso' => 'GD-05', 'codigo' => 'GD-05-07', 'nombre' => 'Bienestar del Alumnado',                              'orden' => 7],
            ['proceso' => 'GD-05', 'codigo' => 'GD-05-08', 'nombre' => 'Manejo de Conflictos',                                'orden' => 8],
            // GD-06
            ['proceso' => 'GD-06', 'codigo' => 'GD-06-01', 'nombre' => 'Padres de Familia',                                   'orden' => 1],
            ['proceso' => 'GD-06', 'codigo' => 'GD-06-02', 'nombre' => 'Autoridades Educativas',                              'orden' => 2],
            ['proceso' => 'GD-06', 'codigo' => 'GD-06-03', 'nombre' => 'Otras Instituciones',                                 'orden' => 3],
            ['proceso' => 'GD-06', 'codigo' => 'GD-06-04', 'nombre' => 'Sector Productivo',                                   'orden' => 4],
            // GA-01
            ['proceso' => 'GA-01', 'codigo' => 'GA-01-01', 'nombre' => 'Plan de Estudios',                                    'orden' => 1],
            ['proceso' => 'GA-01', 'codigo' => 'GA-01-02', 'nombre' => 'Enfoque Metodológico',                                'orden' => 2],
            ['proceso' => 'GA-01', 'codigo' => 'GA-01-03', 'nombre' => 'Recursos para el Aprendizaje',                        'orden' => 3],
            ['proceso' => 'GA-01', 'codigo' => 'GA-01-04', 'nombre' => 'Jornada Escolar',                                     'orden' => 4],
            ['proceso' => 'GA-01', 'codigo' => 'GA-01-05', 'nombre' => 'Evaluación',                                          'orden' => 5],
            // GA-02
            ['proceso' => 'GA-02', 'codigo' => 'GA-02-01', 'nombre' => 'Opciones Didácticas',                                 'orden' => 1],
            ['proceso' => 'GA-02', 'codigo' => 'GA-02-02', 'nombre' => 'Estrategias para las Tareas Escolares',               'orden' => 2],
            ['proceso' => 'GA-02', 'codigo' => 'GA-02-03', 'nombre' => 'Uso Articulado de Recursos',                          'orden' => 3],
            ['proceso' => 'GA-02', 'codigo' => 'GA-02-04', 'nombre' => 'Uso del Tiempo para el Aprendizaje',                  'orden' => 4],
            // GA-03
            ['proceso' => 'GA-03', 'codigo' => 'GA-03-01', 'nombre' => 'Relación Pedagógica',                                 'orden' => 1],
            ['proceso' => 'GA-03', 'codigo' => 'GA-03-02', 'nombre' => 'Planeación de Clases',                                'orden' => 2],
            ['proceso' => 'GA-03', 'codigo' => 'GA-03-03', 'nombre' => 'Estilo Pedagógico',                                   'orden' => 3],
            ['proceso' => 'GA-03', 'codigo' => 'GA-03-04', 'nombre' => 'Evaluación en el Aula',                               'orden' => 4],
            // GA-04
            ['proceso' => 'GA-04', 'codigo' => 'GA-04-01', 'nombre' => 'Seguimiento a Resultados Académicos',                 'orden' => 1],
            ['proceso' => 'GA-04', 'codigo' => 'GA-04-02', 'nombre' => 'Uso Pedagógico de Evaluaciones Externas',             'orden' => 2],
            ['proceso' => 'GA-04', 'codigo' => 'GA-04-03', 'nombre' => 'Seguimiento a la Asistencia',                         'orden' => 3],
            ['proceso' => 'GA-04', 'codigo' => 'GA-04-04', 'nombre' => 'Actividades de Recuperación',                         'orden' => 4],
            ['proceso' => 'GA-04', 'codigo' => 'GA-04-05', 'nombre' => 'Apoyo Pedagógico para Estudiantes con Dificultades',  'orden' => 5],
            ['proceso' => 'GA-04', 'codigo' => 'GA-04-06', 'nombre' => 'Seguimiento a Egresados',                             'orden' => 6],
            // GAF-01
            ['proceso' => 'GAF-01', 'codigo' => 'GAF-01-01', 'nombre' => 'Proceso de Matrícula',                              'orden' => 1],
            ['proceso' => 'GAF-01', 'codigo' => 'GAF-01-02', 'nombre' => 'Archivo Académico',                                 'orden' => 2],
            ['proceso' => 'GAF-01', 'codigo' => 'GAF-01-03', 'nombre' => 'Boletines de Evaluación',                           'orden' => 3],
            // GAF-02
            ['proceso' => 'GAF-02', 'codigo' => 'GAF-02-01', 'nombre' => 'Mantenimiento de Planta Física',                    'orden' => 1],
            ['proceso' => 'GAF-02', 'codigo' => 'GAF-02-02', 'nombre' => 'Programas para Adecuación y Embellecimiento',       'orden' => 2],
            ['proceso' => 'GAF-02', 'codigo' => 'GAF-02-03', 'nombre' => 'Seguimiento al Uso de Espacios',                    'orden' => 3],
            ['proceso' => 'GAF-02', 'codigo' => 'GAF-02-04', 'nombre' => 'Adquisición de Recursos para el Aprendizaje',       'orden' => 4],
            ['proceso' => 'GAF-02', 'codigo' => 'GAF-02-05', 'nombre' => 'Suministros y Dotación',                            'orden' => 5],
            ['proceso' => 'GAF-02', 'codigo' => 'GAF-02-06', 'nombre' => 'Mantenimiento de Equipos y Recursos para el Aprendizaje', 'orden' => 6],
            ['proceso' => 'GAF-02', 'codigo' => 'GAF-02-07', 'nombre' => 'Seguridad y Protección',                            'orden' => 7],
            // GAF-03
            ['proceso' => 'GAF-03', 'codigo' => 'GAF-03-01', 'nombre' => 'Transporte',                                        'orden' => 1],
            ['proceso' => 'GAF-03', 'codigo' => 'GAF-03-02', 'nombre' => 'Restaurante Escolar',                               'orden' => 2],
            ['proceso' => 'GAF-03', 'codigo' => 'GAF-03-03', 'nombre' => 'Salud Ocupacional',                                 'orden' => 3],
            ['proceso' => 'GAF-03', 'codigo' => 'GAF-03-04', 'nombre' => 'Apoyo a Estudiantes con Necesidades Particulares',  'orden' => 4],
            // GAF-04
            ['proceso' => 'GAF-04', 'codigo' => 'GAF-04-01', 'nombre' => 'Perfiles',                                          'orden' => 1],
            ['proceso' => 'GAF-04', 'codigo' => 'GAF-04-02', 'nombre' => 'Inducción',                                         'orden' => 2],
            ['proceso' => 'GAF-04', 'codigo' => 'GAF-04-03', 'nombre' => 'Formación y Capacitación',                          'orden' => 3],
            ['proceso' => 'GAF-04', 'codigo' => 'GAF-04-04', 'nombre' => 'Asignación Académica',                              'orden' => 4],
            ['proceso' => 'GAF-04', 'codigo' => 'GAF-04-05', 'nombre' => 'Bienestar del Talento Humano',                      'orden' => 5],
            ['proceso' => 'GAF-04', 'codigo' => 'GAF-04-06', 'nombre' => 'Evaluación del Desempeño',                          'orden' => 6],
            // GAF-05
            ['proceso' => 'GAF-05', 'codigo' => 'GAF-05-01', 'nombre' => 'Presupuesto',                                       'orden' => 1],
            ['proceso' => 'GAF-05', 'codigo' => 'GAF-05-02', 'nombre' => 'Contabilidad',                                      'orden' => 2],
            ['proceso' => 'GAF-05', 'codigo' => 'GAF-05-03', 'nombre' => 'Ingresos y Gastos',                                 'orden' => 3],
            ['proceso' => 'GAF-05', 'codigo' => 'GAF-05-04', 'nombre' => 'Control Fiscal',                                    'orden' => 4],
            // GC-01
            ['proceso' => 'GC-01', 'codigo' => 'GC-01-01', 'nombre' => 'Atención Educativa a Grupos Poblacionales Diversos',  'orden' => 1],
            ['proceso' => 'GC-01', 'codigo' => 'GC-01-02', 'nombre' => 'Atención a Estudiantes con Necesidades Educativas Especiales', 'orden' => 2],
            ['proceso' => 'GC-01', 'codigo' => 'GC-01-03', 'nombre' => 'Permanencia Escolar',                                 'orden' => 3],
            // GC-02
            ['proceso' => 'GC-02', 'codigo' => 'GC-02-01', 'nombre' => 'Escuela de Padres',                                   'orden' => 1],
            ['proceso' => 'GC-02', 'codigo' => 'GC-02-02', 'nombre' => 'Oferta de Servicios a la Comunidad',                  'orden' => 2],
            ['proceso' => 'GC-02', 'codigo' => 'GC-02-03', 'nombre' => 'Uso de la Planta Física',                             'orden' => 3],
            ['proceso' => 'GC-02', 'codigo' => 'GC-02-04', 'nombre' => 'Servicio Social Estudiantil',                         'orden' => 4],
            // GC-03
            ['proceso' => 'GC-03', 'codigo' => 'GC-03-01', 'nombre' => 'Participación de los Estudiantes',                    'orden' => 1],
            ['proceso' => 'GC-03', 'codigo' => 'GC-03-02', 'nombre' => 'Participación de los Padres de Familia',              'orden' => 2],
            ['proceso' => 'GC-03', 'codigo' => 'GC-03-03', 'nombre' => 'Asamblea y Consejo de Padres',                        'orden' => 3],
            ['proceso' => 'GC-03', 'codigo' => 'GC-03-04', 'nombre' => 'Participación de Egresados',                          'orden' => 4],
            // GC-04
            ['proceso' => 'GC-04', 'codigo' => 'GC-04-01', 'nombre' => 'Prevención de Riesgos Físicos',                       'orden' => 1],
            ['proceso' => 'GC-04', 'codigo' => 'GC-04-02', 'nombre' => 'Prevención de Riesgos Psicosociales',                 'orden' => 2],
            ['proceso' => 'GC-04', 'codigo' => 'GC-04-03', 'nombre' => 'Programas de Seguridad',                              'orden' => 3],
        ];

        $procesos = Proceso::pluck('id', 'codigo');

        foreach ($componentes as $data) {
            $procesoId = $procesos[$data['proceso']];
            Componente::firstOrCreate(
                ['codigo' => $data['codigo']],
                ['proceso_id' => $procesoId, 'nombre' => $data['nombre'], 'orden' => $data['orden'], 'activo' => true]
            );
        }
    }
}
