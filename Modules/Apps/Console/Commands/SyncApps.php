<?php

namespace Modules\Apps\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\Apps\Entities\App;
use Nwidart\Modules\Facades\Module;

class SyncApps extends Command
{
    protected $signature = 'apps:sync';

    protected $description = 'Registra en la tabla apps los módulos nWidart instalados que no estén ya registrados.';

    public function handle(): int
    {
        $modules = Module::all();
        $creadas = 0;
        $existentes = 0;

        $this->info('Sincronizando módulos nWidart con la tabla apps...');

        foreach ($modules as $name => $module) {
            $slug = Str::slug($name);

            $app = App::firstOrCreate(
                ['slug' => $slug],
                [
                    'nombre'     => $name,
                    'ruta'       => '/' . $slug,
                    'descripcion' => "Módulo {$name}",
                    'habilitada' => false,
                    'orden'      => 99,
                ]
            );

            if ($app->wasRecentlyCreated) {
                $this->line("  <fg=green>+</> Creada: {$name} (slug: {$slug})");
                $creadas++;
            } else {
                $this->line("  <fg=yellow>~</> Existente: {$name}");
                $existentes++;
            }
        }

        $this->info("Sincronización completada: {$creadas} nuevas, {$existentes} ya existentes.");

        return self::SUCCESS;
    }
}
