<?php

namespace Modules\CrudGenerator\Services;

use Illuminate\Console\Command;

class CrudGeneratorService
{
    public function generate(string $name, string $module, Command $command): void
    {
        // 1️⃣ Generar archivo de rutas
        (new RouteGenerator)->generate($name, $module, $command);

        // 2️⃣ Generar vista index.blade.php general
        (new ViewGenerator)->generate($name, $module, $command);

        // 3️⃣ y 4️⃣ Generar componente Livewire y su vista
        (new LivewireGenerator)->generate($name, $module, $command);

        // 5️⃣ Agregar ítem al menú de AdminLTE
        (new MenuGenerator)->generate($name, $module, $command);

        // 6️⃣ Agregar Gate en AuthServiceProvider
        (new GateGenerator)->generate($name, $module, $command);

        // 7️⃣ Insertar permisos en tabla permissions
        (new PermissionGenerator)->generate($name, $module, $command);
    }
}
