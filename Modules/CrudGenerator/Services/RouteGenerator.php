<?php

namespace Modules\CrudGenerator\Services;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RouteGenerator
{
    public function generate(string $name, string $module, Command $command): void
    {
        $modulePath = base_path("Modules/{$module}");
        $moduleLower = strtolower($module);
        $nameLower = strtolower($name);

        $routeFile = "{$modulePath}/routes/web.php";

        // 1️⃣ Asegurar que el archivo exista
        if (!File::exists($routeFile)) {
            File::ensureDirectoryExists(dirname($routeFile));
            File::put($routeFile, "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\n");
        }

        // 2️⃣ Insertar la ruta usando el stub
        $stubPath = module_path('CrudGenerator') . '/resources/stubs/route.stub';
        $content = file_get_contents($stubPath);

        $content = str_replace(
            ['{{nameLower}}', '{{name}}', '{{module}}', '{{moduleLower}}'],
            [$nameLower, $name, $module, $moduleLower],
            $content
        );

        File::append($routeFile, "\n" . $content);
        $command->info("✔ Ruta agregada en routes/web.php");
    }
}
