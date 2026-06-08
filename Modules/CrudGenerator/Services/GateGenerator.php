<?php

namespace Modules\CrudGenerator\Services;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GateGenerator
{
    public function generate(string $name, string $module, Command $command): void
    {
        $nameLower = strtolower($name);

        $stubPath = module_path('CrudGenerator') . '/resources/stubs/gate.stub';
        $gateEntry = file_get_contents($stubPath);

        $gateEntry = str_replace('{{nameLower}}', $nameLower, $gateEntry);

        $authProviderPath = app_path('Providers/AuthServiceProvider.php');
        $marker = '// 📌 [crud-generator-gates] Añadir gates aquí';

        if (!File::exists($authProviderPath)) {
            $command->warn("❗ app/Providers/AuthServiceProvider.php no encontrado.");
            return;
        }

        $authContent = File::get($authProviderPath);

        if (str_contains($authContent, $marker)) {
            $newContent = str_replace($marker, $marker . "\n" . $gateEntry, $authContent);
            File::put($authProviderPath, $newContent);
            $command->info("✔ Gate 'ver-{$nameLower}' insertado en AuthServiceProvider.php");
        } else {
            $command->warn("❗ No se encontró el marcador en AuthServiceProvider.php. Inserta el Gate manualmente.");
        }
    }
}
