<?php

namespace Modules\CrudGenerator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\CrudGenerator\Services\CrudGeneratorService;

class MakeCrudCommand extends Command
{
    protected $signature = 'make:crud {name} {--module=}';
    protected $description = 'Genera un CRUD completo para un módulo específico usando plantillas .stub';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $module = $this->option('module');

        if (!$module) {
            $this->error('❌ Debe especificar el módulo con --module');
            return;
        }

        $this->info("🔧 Generando CRUD para [$name] en el módulo [$module]...");

        // Llamar al servicio principal de generación
        app(CrudGeneratorService::class)->generate($name, $module, $this);

        $this->info("✅ CRUD generado correctamente para [$name] en el módulo [$module]");
    }
}
