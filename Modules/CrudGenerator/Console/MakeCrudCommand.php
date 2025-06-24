<?php

namespace Modules\CrudGenerator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MakeCrudCommand extends Command
{
    protected $signature = 'make:crud {name} {--module=}';
    protected $description = 'Genera un CRUD completo para un módulo específico usando plantillas .stub';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));         // Ej: Bien
        //$name = Str::plural(Str::snake($name));         // Ej: bienes
        $module = $this->option('module');
        $nameLower = strtolower($name); // Ej: bien

        if (!$module) {
            $this->error('Debe especificar el módulo con --module');
            return;
        }

        $this->info("Generando CRUD para [$name] en el módulo [$module]...");

        $modulePath = base_path("Modules/{$module}");
        $moduleLower = strtolower($module);

        // 1️⃣ Asegurar archivo routes/web.php existe
        $routeFile = "{$modulePath}/routes/web.php";
        if (!File::exists($routeFile)) {
            File::ensureDirectoryExists(dirname($routeFile));
            File::put($routeFile, "<?php\n\nuse Illuminate\\Support\\Facades\\route;\n\n");
        }

        // 2️⃣ Generar la ruta en web.php
        $routeContent = $this->getStubContent('route.stub', [
            'nameLower' => strtolower($name),
            'moduleLower' => $moduleLower,
        ]);
        File::append($routeFile, "\n" . $routeContent);
        $this->info("✔ Ruta agregada a routes/web.php");

        // 3️⃣ Generar index.blade.php con estructura AdminLTE
        $bladePath = "{$modulePath}/resources/views/{$nameLower}/index.blade.php";
        if (!File::exists(dirname($bladePath))) {
            File::makeDirectory(dirname($bladePath), 0755, true);
        }
        $bladeContent = $this->getStubContent('index-blade.stub', [
            'name' => $name,
            'nameLower' => $nameLower,
            'moduleLower' => $moduleLower,
        ]);
        File::put($bladePath, $bladeContent);
        $this->info("✔ index.blade.php creado en resources/views/{$nameLower}");

        // 4️⃣ Generar componente Livewire NombreIndex.php
        $livewireClassPath = "{$modulePath}/Livewire/{$name}/{$name}Index.php";
        if (!File::exists(dirname($livewireClassPath))) {
            File::makeDirectory(dirname($livewireClassPath), 0755, true);
        }
        $livewireClass = $this->getStubContent('livewire-class.stub', [
            'name' => $name,
            'nameLower' => $nameLower,
            'module' => $module,
            'moduleLower' => $moduleLower,
        ]);
        File::put($livewireClassPath, $livewireClass);
        $this->info("✔ Componente Livewire creado en Livewire/{$name}Index.php");

        // 5️⃣ Generar vista del componente Livewire
        $livewireViewPath = "{$modulePath}/resources/views/livewire/{$nameLower}/{$nameLower}-index.blade.php";
        if (!File::exists(dirname($livewireViewPath))) {
            File::makeDirectory(dirname($livewireViewPath), 0755, true);
        }
        $livewireView = $this->getStubContent('livewire-view.stub', [
            'nameLower' => $nameLower,
        ]);
        File::put($livewireViewPath, $livewireView);
        $this->info("✔ Vista Livewire creada: resources/views/livewire/{$nameLower}-index.blade.php");

        // 6️⃣ Insertar entrada en el menú de AdminLTE
        $menuItem = $this->getStubContent('menu-item.stub', [
            'name' => $name,
            'nameLower' => $nameLower,
            'moduleLower' => $moduleLower,
        ]);

        $adminlteConfigPath = config_path('adminlte.php');
        if (File::exists($adminlteConfigPath)) {
            $configContent = File::get($adminlteConfigPath);
            $marker = '// 📌 [crud-generator-menus] Añadir menús aquí';

            if (str_contains($configContent, $marker)) {
                $newContent = str_replace($marker, $marker . "\n" . $menuItem, $configContent);
                File::put($adminlteConfigPath, $newContent);
                $this->info("✔ Menú insertado en config/adminlte.php");
            } else {
                $this->warn("❗ No se encontró el marcador en config/adminlte.php. Inserte manualmente el menú.");
            }
        } else {
            $this->warn("❗ config/adminlte.php no encontrado.");
        }

        // 7️⃣ Insertar Gate en AuthServiceProvider.php
        $authServiceProviderPath = app_path('Providers/AuthServiceProvider.php');
        if (File::exists($authServiceProviderPath)) {
            $authContent = File::get($authServiceProviderPath);
            $marker = '// 📌 [crud-generator-gates] Añadir gates aquí';

            $gateEntry = $this->getStubContent('gate.stub', [
                'nameLower' => $nameLower,
            ]);

            if (str_contains($authContent, $marker)) {
                $newAuthContent = str_replace($marker, $marker . "\n" . $gateEntry, $authContent);
                File::put($authServiceProviderPath, $newAuthContent);
                $this->info("✔ Gate 'ver-{$nameLower}' insertado en AuthServiceProvider.php");
            } else {
                $this->warn("❗ No se encontró el marcador en AuthServiceProvider.php. Inserte manualmente el Gate.");
            }
        } else {
            $this->warn("❗ app/Providers/AuthServiceProvider.php no encontrado.");
        }

        // 8️⃣ Generar permisos en tabla permissions
        $this->info("🔑 Generando permisos para [$nameLower]...");

        // Leer el contenido del stub con reemplazos
        $permissionsJson = $this->getStubContent('permissions.stub', [
            'nameLower' => $nameLower,
            'name' => $name,
            'module' => $module,
        ]);

        // Decodificar el JSON completo
        $permissions = json_decode($permissionsJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("❗ Error al decodificar permissions.stub: " . json_last_error_msg());
            return;
        }

        // Insertar o actualizar cada permiso
        foreach ($permissions as $permission) {
            $permission['created_at'] = now();
            $permission['updated_at'] = now();

            \DB::table('permissions')->updateOrInsert(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $this->info("✔ Permisos generados en tabla 'permissions'");


        $this->info("✅ CRUD generado correctamente para [$name] en el módulo [$module]");
    }

    protected function getStubContent($stub, $replacements = [])
    {
        $stubPath = module_path('CrudGenerator') . "/resources/stubs/{$stub}";
        $content = file_get_contents($stubPath);

        foreach ($replacements as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }

        return $content;
    }
}
