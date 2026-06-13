<?php

namespace Modules\AdminSistema\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class AdminSistemaServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name      = 'AdminSistema';
    protected string $nameLower = 'adminsistema';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerLivewireComponents();
    }

    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
    }

    private function registerLivewireComponents(): void
    {
        $namespace = 'Modules\\AdminSistema\\Livewire';
        $path      = module_path('AdminSistema', 'Livewire');

        if (!File::exists($path)) {
            return;
        }

        foreach (File::allFiles($path) as $component) {
            $relativePath = $component->getRelativePathname();
            $class        = $namespace . '\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);

            $folder = str_replace(['/', '\\'], '.', dirname($relativePath));
            $folder = ($folder === '.' || $folder === '') ? null : strtolower($folder);

            $alias = $folder
                ? $folder . '.' . $this->kebabCase(pathinfo($relativePath, PATHINFO_FILENAME))
                : $this->kebabCase(pathinfo($relativePath, PATHINFO_FILENAME));

            Livewire::component($alias, $class);
        }
    }

    private function kebabCase(string $string): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $string));
    }

    protected function registerConfig(): void
    {
        $configPath = module_path($this->name, 'config');

        if (!is_dir($configPath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $config    = str_replace($configPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $configKey = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                $key       = ($config === 'config.php') ? $this->nameLower : $this->nameLower . '.' . $configKey;

                $this->publishes([$file->getPathname() => config_path($config)], 'config');
                config([$key => array_replace_recursive(config($key, []), require $file->getPathname())]);
            }
        }
    }

    protected function registerViews(): void
    {
        $sourcePath = module_path($this->name, 'resources/views');
        $this->loadViewsFrom($sourcePath, $this->nameLower);

        Blade::componentNamespace(
            config('modules.namespace') . '\\' . $this->name . '\\View\\Components',
            $this->nameLower
        );
    }
}
