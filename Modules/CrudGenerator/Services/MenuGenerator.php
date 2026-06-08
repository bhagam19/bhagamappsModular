<?php

namespace Modules\CrudGenerator\Services;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MenuGenerator
{
    public function generate(string $name, string $module, Command $command): void
    {
        $nameLower = strtolower($name);
        $moduleLower = strtolower($module);

        $stubPath = module_path('CrudGenerator') . '/resources/stubs/menu-item.stub';
        $menuItem = file_get_contents($stubPath);

        $menuItem = str_replace(
            ['{{name}}', '{{nameLower}}', '{{moduleLower}}'],
            [$name, $nameLower, $moduleLower],
            $menuItem
        );

        $adminlteConfigPath = config_path('adminlte.php');
        $marker = '// 📌 [crud-generator-menus] Añadir menús aquí';

        if (!File::exists($adminlteConfigPath)) {
            $command->warn("❗ config/adminlte.php no encontrado.");
            return;
        }

        $configContent = File::get($adminlteConfigPath);

        if (str_contains($configContent, $marker)) {
            $newContent = str_replace($marker, $marker . "\n" . $menuItem, $configContent);
            File::put($adminlteConfigPath, $newContent);
            $command->info("✔ Menú insertado en config/adminlte.php");
        } else {
            $command->warn("❗ No se encontró el marcador en config/adminlte.php. Inserta el menú manualmente.");
        }
    }
}
