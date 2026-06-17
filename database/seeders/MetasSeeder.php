<?php

namespace Database\Seeders;

use App\Models\Componente;
use App\Models\Meta;
use App\Models\Objetivo;
use Illuminate\Database\Seeder;

class MetasSeeder extends Seeder
{
    public function run(): void
    {
        // Componente 'Servicios Complementarios' no existe en Guía 34.
        // DDOM-GESTION-MAP-001 usa ese nombre para GAF-03.
        // Se usa 'Apoyo a Estudiantes con Necesidades Particulares' (GAF-03-04)
        // como componente más representativo. Registrado en AUDIT-GESTION-PLAN-002.
        $metas = [
            // GD-01
            ['obj' => 'OBJ-GD-01', 'comp' => 'Conocimiento y Apropiación del Direccionamiento', 'codigo' => 'META-GD01-001', 'nombre' => 'Actualizar y socializar el direccionamiento institucional al 100% de la comunidad educativa.',             'unidad' => '%',        'valor' => 100],
            ['obj' => 'OBJ-GD-01', 'comp' => 'Metas Institucionales',                           'codigo' => 'META-GD01-002', 'nombre' => 'Mantener alineados el 100% de los proyectos institucionales con el PEI.',                                 'unidad' => '%',        'valor' => 100],
            // GD-02
            ['obj' => 'OBJ-GD-02', 'comp' => 'Seguimiento y Autoevaluación',                    'codigo' => 'META-GD02-001', 'nombre' => 'Realizar seguimiento trimestral al 100% de los planes institucionales.',                                   'unidad' => '%',        'valor' => 100],
            ['obj' => 'OBJ-GD-02', 'comp' => 'Seguimiento y Autoevaluación',                    'codigo' => 'META-GD02-002', 'nombre' => 'Lograr cumplimiento anual mínimo del 90% de las acciones planificadas.',                                    'unidad' => '%',        'valor' =>  90],
            // GD-03
            ['obj' => 'OBJ-GD-03', 'comp' => 'Consejo Directivo',                               'codigo' => 'META-GD03-001', 'nombre' => 'Garantizar la conformación anual del 100% de los órganos de gobierno escolar.',                            'unidad' => '%',        'valor' => 100],
            ['obj' => 'OBJ-GD-03', 'comp' => 'Consejo Directivo',                               'codigo' => 'META-GD03-002', 'nombre' => 'Alcanzar una participación mínima del 80% en procesos democráticos institucionales.',                       'unidad' => '%',        'valor' =>  80],
            // GD-04
            ['obj' => 'OBJ-GD-04', 'comp' => 'Reconocimiento de Logros',                        'codigo' => 'META-GD04-001', 'nombre' => 'Implementar al menos 4 estrategias anuales de fortalecimiento de cultura institucional.',                   'unidad' => 'Cantidad', 'valor' =>   4],
            ['obj' => 'OBJ-GD-04', 'comp' => 'Reconocimiento de Logros',                        'codigo' => 'META-GD04-002', 'nombre' => 'Reconocer públicamente el 100% de las buenas prácticas institucionales destacadas.',                        'unidad' => '%',        'valor' => 100],
            // GD-05
            ['obj' => 'OBJ-GD-05', 'comp' => 'Manual de Convivencia',                           'codigo' => 'META-GD05-001', 'nombre' => 'Mantener los incidentes graves de convivencia por debajo del 2% anual.',                                   'unidad' => '%',        'valor' =>   2],
            ['obj' => 'OBJ-GD-05', 'comp' => 'Manual de Convivencia',                           'codigo' => 'META-GD05-002', 'nombre' => 'Lograr percepción positiva del clima escolar superior al 85%.',                                             'unidad' => '%',        'valor' =>  85],
            // GD-06
            ['obj' => 'OBJ-GD-06', 'comp' => 'Otras Instituciones',                             'codigo' => 'META-GD06-001', 'nombre' => 'Formalizar al menos 5 alianzas estratégicas activas por año.',                                             'unidad' => 'Cantidad', 'valor' =>   5],
            ['obj' => 'OBJ-GD-06', 'comp' => 'Otras Instituciones',                             'codigo' => 'META-GD06-002', 'nombre' => 'Desarrollar mínimo 4 actividades anuales con actores externos.',                                           'unidad' => 'Cantidad', 'valor' =>   4],
            // GA-01
            ['obj' => 'OBJ-GA-01', 'comp' => 'Plan de Estudios',                                'codigo' => 'META-GA01-001', 'nombre' => 'Actualizar anualmente el 100% de los planes de área.',                                                     'unidad' => '%',        'valor' => 100],
            ['obj' => 'OBJ-GA-01', 'comp' => 'Plan de Estudios',                                'codigo' => 'META-GA01-002', 'nombre' => 'Garantizar disponibilidad de recursos pedagógicos para el 100% de las áreas.',                              'unidad' => '%',        'valor' => 100],
            // GA-02
            ['obj' => 'OBJ-GA-02', 'comp' => 'Opciones Didácticas',                             'codigo' => 'META-GA02-001', 'nombre' => 'Implementar estrategias didácticas innovadoras en el 100% de las áreas.',                                   'unidad' => '%',        'valor' => 100],
            ['obj' => 'OBJ-GA-02', 'comp' => 'Opciones Didácticas',                             'codigo' => 'META-GA02-002', 'nombre' => 'Lograr satisfacción docente superior al 85% respecto a recursos pedagógicos.',                              'unidad' => '%',        'valor' =>  85],
            // GA-03
            ['obj' => 'OBJ-GA-03', 'comp' => 'Planeación de Clases',                            'codigo' => 'META-GA03-001', 'nombre' => 'Garantizar planeación de clases en el 100% de los grupos.',                                               'unidad' => '%',        'valor' => 100],
            ['obj' => 'OBJ-GA-03', 'comp' => 'Planeación de Clases',                            'codigo' => 'META-GA03-002', 'nombre' => 'Alcanzar cumplimiento superior al 90% en observaciones pedagógicas.',                                       'unidad' => '%',        'valor' =>  90],
            // GA-04
            ['obj' => 'OBJ-GA-04', 'comp' => 'Seguimiento a Resultados Académicos',             'codigo' => 'META-GA04-001', 'nombre' => 'Reducir la repitencia escolar al 3%.',                                                                     'unidad' => '%',        'valor' =>   3],
            ['obj' => 'OBJ-GA-04', 'comp' => 'Seguimiento a Resultados Académicos',             'codigo' => 'META-GA04-002', 'nombre' => 'Incrementar la promoción escolar al 95%.',                                                                  'unidad' => '%',        'valor' =>  95],
            ['obj' => 'OBJ-GA-04', 'comp' => 'Seguimiento a Resultados Académicos',             'codigo' => 'META-GA04-003', 'nombre' => 'Reducir la deserción escolar al 2%.',                                                                      'unidad' => '%',        'valor' =>   2],
            // GAF-01
            ['obj' => 'OBJ-GAF-01', 'comp' => 'Archivo Académico',                              'codigo' => 'META-GAF01-001', 'nombre' => 'Mantener actualizada la información académica institucional al 100%.',                                     'unidad' => '%',        'valor' => 100],
            ['obj' => 'OBJ-GAF-01', 'comp' => 'Archivo Académico',                              'codigo' => 'META-GAF01-002', 'nombre' => 'Entregar boletines académicos oportunamente al 100% de los estudiantes.',                                  'unidad' => '%',        'valor' => 100],
            // GAF-02
            ['obj' => 'OBJ-GAF-02', 'comp' => 'Seguimiento al Uso de Espacios',                 'codigo' => 'META-GAF02-001', 'nombre' => 'Mantener inventariado el 100% de los espacios institucionales.',                                          'unidad' => '%',        'valor' => 100],
            ['obj' => 'OBJ-GAF-02', 'comp' => 'Suministros y Dotación',                         'codigo' => 'META-GAF02-002', 'nombre' => 'Mantener actualizado el 100% de los bienes institucionales.',                                             'unidad' => '%',        'valor' => 100],
            ['obj' => 'OBJ-GAF-02', 'comp' => 'Mantenimiento de Equipos y Recursos para el Aprendizaje', 'codigo' => 'META-GAF02-003', 'nombre' => 'Ejecutar el 100% del plan anual de mantenimiento.',                                             'unidad' => '%',        'valor' => 100],
            // GAF-03
            ['obj' => 'OBJ-GAF-03', 'comp' => 'Apoyo a Estudiantes con Necesidades Particulares', 'codigo' => 'META-GAF03-001', 'nombre' => 'Garantizar cobertura del 100% de los servicios complementarios priorizados.',                           'unidad' => '%',        'valor' => 100],
            ['obj' => 'OBJ-GAF-03', 'comp' => 'Apoyo a Estudiantes con Necesidades Particulares', 'codigo' => 'META-GAF03-002', 'nombre' => 'Lograr satisfacción superior al 85% en servicios institucionales.',                                     'unidad' => '%',        'valor' =>  85],
            // GAF-04
            ['obj' => 'OBJ-GAF-04', 'comp' => 'Formación y Capacitación',                       'codigo' => 'META-GAF04-001', 'nombre' => 'Garantizar procesos de inducción para el 100% del personal nuevo.',                                       'unidad' => '%',        'valor' => 100],
            ['obj' => 'OBJ-GAF-04', 'comp' => 'Formación y Capacitación',                       'codigo' => 'META-GAF04-002', 'nombre' => 'Ejecutar mínimo dos jornadas anuales de capacitación.',                                                   'unidad' => 'Cantidad', 'valor' =>   2],
            // GAF-05
            ['obj' => 'OBJ-GAF-05', 'comp' => 'Presupuesto',                                    'codigo' => 'META-GAF05-001', 'nombre' => 'Ejecutar el presupuesto institucional con eficiencia superior al 90%.',                                   'unidad' => '%',        'valor' =>  90],
            ['obj' => 'OBJ-GAF-05', 'comp' => 'Presupuesto',                                    'codigo' => 'META-GAF05-002', 'nombre' => 'Mantener cumplimiento del 100% de obligaciones contables.',                                               'unidad' => '%',        'valor' => 100],
            // GC-01
            ['obj' => 'OBJ-GC-01', 'comp' => 'Permanencia Escolar',                             'codigo' => 'META-GC01-001',  'nombre' => 'Mantener cobertura escolar superior al 95%.',                                                            'unidad' => '%',        'valor' =>  95],
            ['obj' => 'OBJ-GC-01', 'comp' => 'Permanencia Escolar',                             'codigo' => 'META-GC01-002',  'nombre' => 'Garantizar atención al 100% de estudiantes con necesidades identificadas.',                               'unidad' => '%',        'valor' => 100],
            // GC-02
            ['obj' => 'OBJ-GC-02', 'comp' => 'Oferta de Servicios a la Comunidad',              'codigo' => 'META-GC02-001',  'nombre' => 'Realizar mínimo cuatro actividades anuales de proyección comunitaria.',                                   'unidad' => 'Cantidad', 'valor' =>   4],
            ['obj' => 'OBJ-GC-02', 'comp' => 'Oferta de Servicios a la Comunidad',              'codigo' => 'META-GC02-002',  'nombre' => 'Incrementar la participación comunitaria en un 10% anual.',                                               'unidad' => '%',        'valor' =>  10],
            // GC-03
            ['obj' => 'OBJ-GC-03', 'comp' => 'Participación de los Padres de Familia',          'codigo' => 'META-GC03-001',  'nombre' => 'Lograr participación superior al 80% de familias en actividades institucionales.',                        'unidad' => '%',        'valor' =>  80],
            ['obj' => 'OBJ-GC-03', 'comp' => 'Participación de los Padres de Familia',          'codigo' => 'META-GC03-002',  'nombre' => 'Mantener percepción positiva de convivencia superior al 85%.',                                            'unidad' => '%',        'valor' =>  85],
            // GC-04
            ['obj' => 'OBJ-GC-04', 'comp' => 'Programas de Seguridad',                          'codigo' => 'META-GC04-001',  'nombre' => 'Ejecutar el 100% de las actividades del plan de prevención de riesgos.',                                 'unidad' => '%',        'valor' => 100],
            ['obj' => 'OBJ-GC-04', 'comp' => 'Programas de Seguridad',                          'codigo' => 'META-GC04-002',  'nombre' => 'Reducir incidentes de riesgo institucional en un 10% anual.',                                            'unidad' => '%',        'valor' =>  10],
        ];

        $objetivos    = Objetivo::pluck('id', 'codigo');
        $componentes  = Componente::pluck('id', 'nombre');

        foreach ($metas as $data) {
            Meta::firstOrCreate(
                ['codigo' => $data['codigo']],
                [
                    'objetivo_id'   => $objetivos[$data['obj']],
                    'componente_id' => $componentes[$data['comp']],
                    'nombre'        => $data['nombre'],
                    'unidad'        => $data['unidad'],
                    'valor_objetivo' => $data['valor'],
                    'activo'        => true,
                ]
            );
        }
    }
}
