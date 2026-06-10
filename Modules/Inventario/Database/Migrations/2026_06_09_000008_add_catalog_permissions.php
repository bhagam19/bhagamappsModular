<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            ['nombre' => 'Ver Categorías',            'slug' => 'ver-categorias',            'descripcion' => 'Ver catálogo de categorías',            'categoria' => 'catalogos'],
            ['nombre' => 'Crear Categorías',          'slug' => 'crear-categorias',          'descripcion' => 'Crear nuevas categorías',               'categoria' => 'catalogos'],
            ['nombre' => 'Editar Categorías',         'slug' => 'editar-categorias',         'descripcion' => 'Editar categorías existentes',          'categoria' => 'catalogos'],
            ['nombre' => 'Eliminar Categorías',       'slug' => 'eliminar-categorias',       'descripcion' => 'Eliminar categorías del sistema',       'categoria' => 'catalogos'],

            ['nombre' => 'Ver Dependencias',          'slug' => 'ver-dependencias',          'descripcion' => 'Ver catálogo de dependencias',          'categoria' => 'catalogos'],
            ['nombre' => 'Crear Dependencias',        'slug' => 'crear-dependencias',        'descripcion' => 'Crear nuevas dependencias',             'categoria' => 'catalogos'],
            ['nombre' => 'Editar Dependencias',       'slug' => 'editar-dependencias',       'descripcion' => 'Editar dependencias existentes',        'categoria' => 'catalogos'],
            ['nombre' => 'Eliminar Dependencias',     'slug' => 'eliminar-dependencias',     'descripcion' => 'Eliminar dependencias del sistema',     'categoria' => 'catalogos'],

            ['nombre' => 'Ver Ubicaciones',           'slug' => 'ver-ubicaciones',           'descripcion' => 'Ver catálogo de ubicaciones',           'categoria' => 'catalogos'],
            ['nombre' => 'Crear Ubicaciones',         'slug' => 'crear-ubicaciones',         'descripcion' => 'Crear nuevas ubicaciones',              'categoria' => 'catalogos'],
            ['nombre' => 'Editar Ubicaciones',        'slug' => 'editar-ubicaciones',        'descripcion' => 'Editar ubicaciones existentes',         'categoria' => 'catalogos'],
            ['nombre' => 'Eliminar Ubicaciones',      'slug' => 'eliminar-ubicaciones',      'descripcion' => 'Eliminar ubicaciones del sistema',      'categoria' => 'catalogos'],

            ['nombre' => 'Ver Estados de Bien',       'slug' => 'ver-estados',               'descripcion' => 'Ver catálogo de estados de bien',       'categoria' => 'catalogos'],
            ['nombre' => 'Crear Estados de Bien',     'slug' => 'crear-estados',             'descripcion' => 'Crear nuevos estados de bien',          'categoria' => 'catalogos'],
            ['nombre' => 'Editar Estados de Bien',    'slug' => 'editar-estados',            'descripcion' => 'Editar estados de bien existentes',     'categoria' => 'catalogos'],
            ['nombre' => 'Eliminar Estados de Bien',  'slug' => 'eliminar-estados',          'descripcion' => 'Eliminar estados de bien del sistema',  'categoria' => 'catalogos'],

            ['nombre' => 'Ver Orígenes',              'slug' => 'ver-origenes',              'descripcion' => 'Ver catálogo de orígenes de bien',      'categoria' => 'catalogos'],
            ['nombre' => 'Crear Orígenes',            'slug' => 'crear-origenes',            'descripcion' => 'Crear nuevos orígenes de bien',         'categoria' => 'catalogos'],
            ['nombre' => 'Editar Orígenes',           'slug' => 'editar-origenes',           'descripcion' => 'Editar orígenes de bien existentes',    'categoria' => 'catalogos'],
            ['nombre' => 'Eliminar Orígenes',         'slug' => 'eliminar-origenes',         'descripcion' => 'Eliminar orígenes de bien del sistema', 'categoria' => 'catalogos'],

            ['nombre' => 'Ver Almacenamientos',       'slug' => 'ver-almacenamientos',       'descripcion' => 'Ver catálogo de almacenamientos',       'categoria' => 'catalogos'],
            ['nombre' => 'Crear Almacenamientos',     'slug' => 'crear-almacenamientos',     'descripcion' => 'Crear nuevos almacenamientos',          'categoria' => 'catalogos'],
            ['nombre' => 'Editar Almacenamientos',    'slug' => 'editar-almacenamientos',    'descripcion' => 'Editar almacenamientos existentes',     'categoria' => 'catalogos'],
            ['nombre' => 'Eliminar Almacenamientos',  'slug' => 'eliminar-almacenamientos',  'descripcion' => 'Eliminar almacenamientos del sistema',  'categoria' => 'catalogos'],

            ['nombre' => 'Ver Mantenimientos',        'slug' => 'ver-mantenimientos',        'descripcion' => 'Ver catálogo de tipos de mantenimiento','categoria' => 'catalogos'],
            ['nombre' => 'Crear Mantenimientos',      'slug' => 'crear-mantenimientos',      'descripcion' => 'Crear nuevos tipos de mantenimiento',   'categoria' => 'catalogos'],
            ['nombre' => 'Editar Mantenimientos',     'slug' => 'editar-mantenimientos',     'descripcion' => 'Editar tipos de mantenimiento existentes','categoria' => 'catalogos'],
            ['nombre' => 'Eliminar Mantenimientos',   'slug' => 'eliminar-mantenimientos',   'descripcion' => 'Eliminar tipos de mantenimiento',       'categoria' => 'catalogos'],
        ];

        DB::table('permissions')->insertOrIgnore($permissions);

        $allSlugs = array_column($permissions, 'slug');
        $verSlugs = array_filter($allSlugs, fn($s) => str_starts_with($s, 'ver-'));

        $allPermIds    = DB::table('permissions')->whereIn('slug', $allSlugs)->pluck('id');
        $verPermIds    = DB::table('permissions')->whereIn('slug', array_values($verSlugs))->pluck('id');

        $adminId       = DB::table('roles')->where('nombre', 'Administrador')->value('id');
        $rectorId      = DB::table('roles')->where('nombre', 'Rector')->value('id');
        $coordinadorId = DB::table('roles')->where('nombre', 'Coordinador')->value('id');

        $rows = [];
        foreach ($allPermIds as $permId) {
            if ($adminId)  $rows[] = ['permission_id' => $permId, 'role_id' => $adminId];
            if ($rectorId) $rows[] = ['permission_id' => $permId, 'role_id' => $rectorId];
        }
        foreach ($verPermIds as $permId) {
            if ($coordinadorId) $rows[] = ['permission_id' => $permId, 'role_id' => $coordinadorId];
        }

        DB::table('permission_role')->insertOrIgnore($rows);
    }

    public function down(): void
    {
        $slugs = [
            'ver-categorias','crear-categorias','editar-categorias','eliminar-categorias',
            'ver-dependencias','crear-dependencias','editar-dependencias','eliminar-dependencias',
            'ver-ubicaciones','crear-ubicaciones','editar-ubicaciones','eliminar-ubicaciones',
            'ver-estados','crear-estados','editar-estados','eliminar-estados',
            'ver-origenes','crear-origenes','editar-origenes','eliminar-origenes',
            'ver-almacenamientos','crear-almacenamientos','editar-almacenamientos','eliminar-almacenamientos',
            'ver-mantenimientos','crear-mantenimientos','editar-mantenimientos','eliminar-mantenimientos',
        ];
        $ids = DB::table('permissions')->whereIn('slug', $slugs)->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('slug', $slugs)->delete();
    }
};
