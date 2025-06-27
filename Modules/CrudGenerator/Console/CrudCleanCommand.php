<?php

namespace Modules\CrudGenerator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CrudCleanCommand extends Command
{
    protected $signature = 'crud:clean {name} {--module=}';
    protected $description = 'Elimina los archivos generados por make:crud para un módulo específico';

    public function handle()
    {
        $name = Str::studly($this->argument('name')); // Ej: Bien
        $module = $this->option('module');
        $moduleLower = strtolower($module); // Ej: inventario
        $nameLower = strtolower($name); // Ej: bien

        if (!$module) {
            $this->error('Debe especificar el módulo con --module');
            return;
        }

        $modulePath = base_path("Modules/{$module}");

        // 🗑 1️⃣ Limpiar ruta generada en web.php
        $routeFile = "{$modulePath}/routes/web.php";
        if (File::exists($routeFile)) {
            $routeContent = File::get($routeFile);

            // Construimos un patrón que identifique el bloque completo
            $pattern = "/Route::middleware\(\['web', 'auth'\]\)\s*->prefix\('{$moduleLower}'\)\s*->group\(function\s*\(\)\s*\{\s*Route::get\('{$nameLower}',[^\}]+?\}\);/m";

            $newRouteContent = preg_replace($pattern, '', $routeContent);

            // Limpiamos saltos de línea excesivos
            $newRouteContent = preg_replace("/\n{2,}/", "\n\n", $newRouteContent);

            File::put($routeFile, trim($newRouteContent) . "\n");
            $this->info("✔ Ruta eliminada del archivo routes/web.php");
        }

        // 🗑 2️⃣ Eliminar carpeta de la vista Blade
        $bladeFolder = "{$modulePath}/resources/views/{$nameLower}";
        if (File::exists($bladeFolder)) {
            File::deleteDirectory($bladeFolder);
            $this->info("✔ Carpeta eliminada: views/{$nameLower}");
        }

        // 🗑 3️⃣ Eliminar componente Livewire
        $livewireFolder = "{$modulePath}/Livewire/{$name}";
        if (File::exists($livewireFolder)) {
            File::deleteDirectory($livewireFolder);
            $this->info("✔ Carpeta eliminada: Livewire/{$name}");
        }

        // 🗑 4️⃣ Eliminar carpeta de la vista Livewire
        $livewireViewFolder = "{$modulePath}/resources/views/livewire/{$nameLower}";
        if (File::exists($livewireViewFolder)) {
            File::deleteDirectory($livewireViewFolder);
            $this->info("✔ Carpeta eliminada: views/livewire/{$nameLower}");
        }

        // 🗑 4️⃣ Limpiar ruta generada en web.php
        $routeFile = "{$modulePath}/routes/web.php";
        if (File::exists($routeFile)) {
            $routeContent = File::get($routeFile);
            // Buscamos exactamente el texto que generamos desde el stub
            $routeGenerated = "Route::get('/" . strtolower($module) . "/{$nameLower}', fn () => view('" . strtolower($module) . "::{$nameLower}.index'))->name('" . strtolower($module) . ".{$nameLower}.index');";

            $newRouteContent = str_replace($routeGenerated, '', $routeContent);
            // Limpiamos saltos de línea dobles o triples
            $newRouteContent = preg_replace("/\n{2,}/", "\n\n", $newRouteContent);
            File::put($routeFile, trim($newRouteContent) . "\n");
            $this->info("✔ Ruta eliminada del archivo routes/web.php");
        }

        // 5️⃣ Limpiar menú en config/adminlte.php
        $adminlteConfig = config_path('adminlte.php');
        if (File::exists($adminlteConfig)) {
            $menuContent = File::get($adminlteConfig);

            // Buscar bloque por delimitadores específicos
            $pattern = "/\/\/ crud-generator-start:{$nameLower}[\s\S]*?\/\/ crud-generator-end:{$nameLower}\n*/";

            $newMenuContent = preg_replace($pattern, '', $menuContent);
            File::put($adminlteConfig, $newMenuContent);

            $this->info("✔ Menú eliminado de config/adminlte.php");
        }


        // 6️⃣ Limpiar Gate en AuthServiceProvider.php
        $authServiceProviderPath = app_path('Providers/AuthServiceProvider.php');
        if (File::exists($authServiceProviderPath)) {
            $authContent = File::get($authServiceProviderPath);

            $escapedNameLower = preg_quote($nameLower, '/');
            $gatePattern = "/Gate::define\(\s*'ver-{$escapedNameLower}'\s*,\s*function\s*\(\s*\\\$user\s*\)\s*\{\s*return\s*\\\$user->hasPermission\(\s*'ver-{$escapedNameLower}'\s*\);\s*\}\s*\);\s*/m";

            $newAuthContent = preg_replace($gatePattern, '', $authContent);

            File::put($authServiceProviderPath, $newAuthContent);
            $this->info("✔ Gate eliminado de AuthServiceProvider.php");
        }

        // 7️⃣ Eliminar permisos generados
        try {
            DB::table('permissions')->whereIn('slug', [
                "ver-{$nameLower}",
                "crear-{$nameLower}",
                "editar-{$nameLower}",
                "eliminar-{$nameLower}",
            ])->delete();

            $this->info("✔ Permisos eliminados de la tabla 'permissions'");
        } catch (\Throwable $e) {
            $this->warn("⚠ No se pudieron eliminar los permisos (quizás la tabla 'permissions' no existe aún). Error: {$e->getMessage()}");
        }


        $this->info("✅ Limpieza completada para [$name] en el módulo [$module]");
    }
}
