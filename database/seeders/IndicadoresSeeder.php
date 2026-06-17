<?php

namespace Database\Seeders;

use App\Models\Componente;
use App\Models\Indicador;
use Illuminate\Database\Seeder;

class IndicadoresSeeder extends Seeder
{
    public function run(): void
    {
        // 'Servicios Complementarios' no existe en Guía 34.
        // IND-ADM-005 se asigna a 'Apoyo a Estudiantes con Necesidades Particulares' (GAF-03-04).
        // Registrado en AUDIT-GESTION-PLAN-002.
        $indicadores = [
            // IND-DIR
            ['comp' => 'Conocimiento y Apropiación del Direccionamiento', 'codigo' => 'IND-DIR-001', 'nombre' => 'Cobertura de Socialización Institucional',      'descripcion' => 'Porcentaje de miembros de la comunidad que conocen el direccionamiento institucional.', 'unidad' => '%',        'frecuencia' => 'Anual',     'tipo' => 'simple',   'fuente' => 'Manual'],
            ['comp' => 'Metas Institucionales',                           'codigo' => 'IND-DIR-002', 'nombre' => 'Cumplimiento de Planes Institucionales',         'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Trimestral', 'tipo' => 'simple',   'fuente' => 'Planeación'],
            ['comp' => 'Consejo Directivo',                               'codigo' => 'IND-DIR-003', 'nombre' => 'Participación en Gobierno Escolar',              'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Anual',     'tipo' => 'simple',   'fuente' => 'Comunidad Educativa'],
            ['comp' => 'Reconocimiento de Logros',                        'codigo' => 'IND-DIR-004', 'nombre' => 'Percepción de Clima Escolar',                    'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Semestral', 'tipo' => 'simple',   'fuente' => 'Encuestas'],
            ['comp' => 'Otras Instituciones',                             'codigo' => 'IND-DIR-005', 'nombre' => 'Alianzas Institucionales Activas',               'descripcion' => null,                                                                                    'unidad' => 'Cantidad', 'frecuencia' => 'Anual',     'tipo' => 'simple',   'fuente' => 'Manual'],
            // IND-ACA
            ['comp' => 'Plan de Estudios',                                'codigo' => 'IND-ACA-001', 'nombre' => 'Actualización de Planes de Área',                'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Anual',     'tipo' => 'simple',   'fuente' => 'Académico'],
            ['comp' => 'Opciones Didácticas',                             'codigo' => 'IND-ACA-002', 'nombre' => 'Implementación de Estrategias Didácticas',       'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Semestral', 'tipo' => 'simple',   'fuente' => 'Académico'],
            ['comp' => 'Planeación de Clases',                            'codigo' => 'IND-ACA-003', 'nombre' => 'Planeación de Clases',                           'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Mensual',   'tipo' => 'simple',   'fuente' => 'Académico'],
            ['comp' => 'Seguimiento a Resultados Académicos',             'codigo' => 'IND-ACA-004', 'nombre' => 'Tasa de Repitencia',                             'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Anual',     'tipo' => 'simple',   'fuente' => 'Académico'],
            ['comp' => 'Seguimiento a Resultados Académicos',             'codigo' => 'IND-ACA-005', 'nombre' => 'Tasa de Promoción',                              'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Anual',     'tipo' => 'simple',   'fuente' => 'Académico'],
            ['comp' => 'Seguimiento a Resultados Académicos',             'codigo' => 'IND-ACA-006', 'nombre' => 'Tasa de Deserción',                              'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Anual',     'tipo' => 'simple',   'fuente' => 'Académico'],
            ['comp' => 'Seguimiento a Resultados Académicos',             'codigo' => 'IND-ACA-007', 'nombre' => 'Tasa de Asistencia',                             'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Mensual',   'tipo' => 'simple',   'fuente' => 'Académico'],
            // IND-ADM
            ['comp' => 'Archivo Académico',                               'codigo' => 'IND-ADM-001', 'nombre' => 'Actualización de Información Académica',         'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Mensual',   'tipo' => 'simple',   'fuente' => 'Académico'],
            ['comp' => 'Seguimiento al Uso de Espacios',                  'codigo' => 'IND-ADM-002', 'nombre' => 'Cobertura de Inventario Institucional',          'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Mensual',   'tipo' => 'simple',   'fuente' => 'Inventario'],
            ['comp' => 'Suministros y Dotación',                          'codigo' => 'IND-ADM-003', 'nombre' => 'Actualización de Bienes',                        'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Mensual',   'tipo' => 'simple',   'fuente' => 'Inventario'],
            ['comp' => 'Mantenimiento de Equipos y Recursos para el Aprendizaje', 'codigo' => 'IND-ADM-004', 'nombre' => 'Cumplimiento del Plan de Mantenimiento', 'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Mensual',   'tipo' => 'simple',   'fuente' => 'Inventario'],
            ['comp' => 'Apoyo a Estudiantes con Necesidades Particulares','codigo' => 'IND-ADM-005', 'nombre' => 'Cobertura de Servicios Complementarios',         'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Mensual',   'tipo' => 'simple',   'fuente' => 'Manual'],
            ['comp' => 'Formación y Capacitación',                        'codigo' => 'IND-ADM-006', 'nombre' => 'Cobertura de Capacitación',                      'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Anual',     'tipo' => 'simple',   'fuente' => 'Talento Humano'],
            ['comp' => 'Presupuesto',                                     'codigo' => 'IND-ADM-007', 'nombre' => 'Ejecución Presupuestal',                         'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Mensual',   'tipo' => 'simple',   'fuente' => 'Financiero'],
            // IND-COM
            ['comp' => 'Permanencia Escolar',                             'codigo' => 'IND-COM-001', 'nombre' => 'Cobertura Escolar',                              'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Anual',     'tipo' => 'simple',   'fuente' => 'Comunidad Educativa'],
            ['comp' => 'Permanencia Escolar',                             'codigo' => 'IND-COM-002', 'nombre' => 'Atención a Necesidades Educativas',              'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Semestral', 'tipo' => 'simple',   'fuente' => 'Comunidad Educativa'],
            ['comp' => 'Oferta de Servicios a la Comunidad',              'codigo' => 'IND-COM-003', 'nombre' => 'Participación Comunitaria',                      'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Semestral', 'tipo' => 'simple',   'fuente' => 'Comunidad Educativa'],
            ['comp' => 'Participación de los Padres de Familia',          'codigo' => 'IND-COM-004', 'nombre' => 'Participación Familiar',                         'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Semestral', 'tipo' => 'simple',   'fuente' => 'Comunidad Educativa'],
            ['comp' => 'Programas de Seguridad',                          'codigo' => 'IND-COM-005', 'nombre' => 'Cumplimiento del Plan de Riesgos',               'descripcion' => null,                                                                                    'unidad' => '%',        'frecuencia' => 'Mensual',   'tipo' => 'simple',   'fuente' => 'Manual'],
            ['comp' => 'Programas de Seguridad',                          'codigo' => 'IND-COM-006', 'nombre' => 'Incidentes de Riesgo',                           'descripcion' => null,                                                                                    'unidad' => 'Cantidad', 'frecuencia' => 'Mensual',   'tipo' => 'simple',   'fuente' => 'Convivencia'],
        ];

        $componentes = Componente::pluck('id', 'nombre');

        foreach ($indicadores as $data) {
            Indicador::firstOrCreate(
                ['codigo' => $data['codigo']],
                [
                    'componente_id' => $componentes[$data['comp']],
                    'nombre'        => $data['nombre'],
                    'descripcion'   => $data['descripcion'],
                    'unidad'        => $data['unidad'],
                    'frecuencia'    => $data['frecuencia'],
                    'tipo'          => $data['tipo'],
                    'fuente_dato'   => $data['fuente'],
                    'activo'        => true,
                ]
            );
        }
    }
}
