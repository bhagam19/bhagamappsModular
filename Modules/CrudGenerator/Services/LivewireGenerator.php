<?php

namespace Modules\CrudGenerator\Services;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LivewireGenerator
{
    public function generate(string $name, string $module, Command $command): void
    {
        $modulePath = base_path("Modules/{$module}");
        $moduleLower = strtolower($module);
        $nameLower = strtolower($name);
        $modelPath = "{$modulePath}/Entities/{$name}.php";

        $relationNames = [];
        $fillableFields = [];

        if (File::exists($modelPath)) {
            $modelContent = File::get($modelPath);

            if (preg_match('/\$fillable\s*=\s*\[([^\]]+)\]/', $modelContent, $matches)) {
                preg_match_all('/\'([^\']+)\'/', $matches[1], $fields);
                $fillableFields = $fields[1] ?? [];
                $relationNames = array_filter($fillableFields, fn($field) => Str::endsWith($field, '_id'));
            }
        }

        $externalModels = ['user', 'role', 'apps'];

        $relationUseModels = '';
        $relationViewData  = '';
        $relationFields    = '';

        foreach ($relationNames as $field) {
            $relation = Str::before($field, '_id');
            $class = Str::studly($relation);
            $variable = "{$relation}Options";

            $moduleTarget = in_array($relation, $externalModels) ? Str::studly($relation) : $module;
            $modelClass = "\\Modules\\{$moduleTarget}\\Entities\\{$class}";

            if (!class_exists($modelClass)) {
                $command->warn("❗ Clase {$modelClass} no encontrada. Saltando...");
                continue;
            }

            $tableName = (new $modelClass)->getTable();

            $orderBy = Schema::hasColumn($tableName, 'nombre') ? 'nombre' : 'id';

            $relationUseModels  .= "use Modules\\{$moduleTarget}\\Entities\\{$class};\n";
            $relationViewData  .= $relation === 'user'
                ? "            '{$variable}' => {$class}::all()->sortBy('nombre_completo'),\n"
                : "            '{$variable}' => {$class}::orderBy('{$orderBy}')->get(),\n";

            $relationFields .= "        '{$field}' => '" . ($relation === 'user' ? 'nombre_completo' : 'nombre') . "',\n";
        }

        $rules = (new RuleGenerator)->generate($module, $name);

        // Crear directorio para clase
        $livewireClassPath = "{$modulePath}/Livewire/{$name}/{$name}Index.php";
        File::ensureDirectoryExists(dirname($livewireClassPath));

        $stubClassPath = module_path('CrudGenerator') . '/resources/stubs/livewire-class.stub';
        $classContent = file_get_contents($stubClassPath);
        $classContent = str_replace([
            '{{name}}',
            '{{nameLower}}',
            '{{module}}',
            '{{moduleLower}}',
            '{{relationViewData}}',
            '{{relationUseModels}}',
            '{{relationFields}}',
            '{{rules}}'
        ], [
            $name,
            $nameLower,
            $module,
            $moduleLower,
            rtrim($relationViewData),
            rtrim($relationUseModels),
            rtrim($relationFields),
            $rules
        ], $classContent);
        File::put($livewireClassPath, $classContent);
        $command->info("✔ Componente Livewire creado: Livewire/{$name}/{$name}Index.php");

        // Crear vista Livewire
        $livewireViewPath = "{$modulePath}/resources/views/livewire/{$nameLower}/{$nameLower}-index.blade.php";
        File::ensureDirectoryExists(dirname($livewireViewPath));

        $stubViewPath = module_path('CrudGenerator') . '/resources/stubs/livewire-view.stub';
        $viewContent = file_get_contents($stubViewPath);
        $viewContent = str_replace('{{nameLower}}', $nameLower, $viewContent);
        File::put($livewireViewPath, $viewContent);
        $command->info("✔ Vista Livewire creada: resources/views/livewire/{$nameLower}/{$nameLower}-index.blade.php");
    }
}
