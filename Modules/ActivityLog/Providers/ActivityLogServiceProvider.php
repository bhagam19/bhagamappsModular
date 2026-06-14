<?php

namespace Modules\ActivityLog\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Nwidart\Modules\Traits\PathNamespace;

class ActivityLogServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name      = 'ActivityLog';
    protected string $nameLower = 'activitylog';

    public function boot(): void
    {
        $this->registerViews();
        $this->registerLivewireComponents();
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        // Cargar helper global activity_log()
        $helper = module_path('ActivityLog', 'helpers.php');
        if (File::exists($helper)) {
            require_once $helper;
        }
    }

    private function registerLivewireComponents(): void
    {
        $namespace = 'Modules\\ActivityLog\\Livewire';
        $path      = module_path('ActivityLog', 'Livewire');

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

    protected function registerViews(): void
    {
        $sourcePath = module_path($this->name, 'resources/views');
        $this->loadViewsFrom($sourcePath, $this->nameLower);

        Blade::componentNamespace(
            'Modules\\' . $this->name . '\\View\\Components',
            $this->nameLower
        );
    }
}
