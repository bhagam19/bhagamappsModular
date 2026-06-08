<?php

namespace Modules\CrudGenerator\Services;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ViewGenerator
{
    public function generate(string $name, string $module, Command $command): void
    {
        $modulePath = base_path("Modules/{$module}");
        $nameLower = strtolower($name);

        // Crear carpeta si no existe
        $viewPath = "{$modulePath}/resources/views/{$nameLower}";
        File::ensureDirectoryExists($viewPath);

        // Ruta final del archivo
        $bladePath = "{$viewPath}/index.blade.php";

        // Leer el stub y reemplazar variables
        $stubPath = module_path('CrudGenerator') . '/resources/stubs/index-blade.stub';
        $content = file_get_contents($stubPath);

        $content = str_replace(
            ['{{name}}', '{{nameLower}}', '{{moduleLower}}'],
            [$name, $nameLower, strtolower($module)],
            $content
        );

        File::put($bladePath, $content);
        $command->info("✔ Vista index.blade.php creada en resources/views/{$nameLower}/index.blade.php");
    }
}
