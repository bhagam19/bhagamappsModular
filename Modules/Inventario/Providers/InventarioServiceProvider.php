<?php

namespace Modules\Inventario\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use Livewire\Livewire;
use Illuminate\Support\Facades\File;
/*
use Modules\Inventario\Livewire\Bienes\BienesIndex;
use Modules\Inventario\Livewire\Bienes\EditarCampoBien;
use Modules\Inventario\Livewire\Bienes\EditarDetalleBien;
use Modules\Inventario\Livewire\Notifications\Notificaciones;
use Modules\Inventario\Livewire\Notifications\NotificacionesIcono;
*/

class InventarioServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Inventario';
    protected string $nameLower = 'inventario';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {                
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'Database/Migrations'));

        $namespace = 'Modules\\Inventario\\Livewire';

        $path = module_path('Inventario', 'Livewire');

        if (!File::exists($path)) {
            return;
        }

        $components = File::allFiles($path);

        foreach ($components as $component) {
            $relativePath = $component->getRelativePathname();

            $class = $namespace . '\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);

            $folder = str_replace(['/', '\\'], '.', dirname($relativePath));
            if ($folder === '.' || $folder === '') {
                $folder = null;
            } else {
                $folder = strtolower($folder);
            }

            $filename = pathinfo($relativePath, PATHINFO_FILENAME);

            $aliasName = $this->kebabCase($filename);

            $alias = $folder ? $folder . '.' . $aliasName : $aliasName;

            Livewire::component($alias, $class);
        } 

        /*
        Livewire::component('bienes.bienes-index', BienesIndex::class);
        Livewire::component('bienes.editar-campo-bien', EditarCampoBien::class);
        Livewire::component('bienes.editar-detalle-bien', EditarDetalleBien::class);
        Livewire::component('notifications.notificaciones', Notificaciones::class);
        Livewire::component('notifications.notificaciones-icono', NotificacionesIcono::class);
        */
    }

    private function kebabCase(string $string): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $string));
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $configPath = module_path($this->name, config('modules.paths.generator.config.path'));

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $config = str_replace($configPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $config_key = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                    $segments = explode('.', $this->nameLower.'.'.$config_key);

                    // Remove duplicated adjacent segments
                    $normalized = [];
                    foreach ($segments as $segment) {
                        if (end($normalized) !== $segment) {
                            $normalized[] = $segment;
                        }
                    }

                    $key = ($config === 'config.php') ? $this->nameLower : implode('.', $normalized);

                    $this->publishes([$file->getPathname() => config_path($config)], 'config');
                    $this->merge_config_from($file->getPathname(), $key);
                }
            }
        }
    }

    /**
     * Merge config from the given path recursively.
     */
    protected function merge_config_from(string $path, string $key): void
    {
        $existing = config($key, []);
        $module_config = require $path;

        config([$key => array_replace_recursive($existing, $module_config)]);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        Blade::componentNamespace(config('modules.namespace').'\\' . $this->name . '\\View\\Components', $this->nameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }
}
