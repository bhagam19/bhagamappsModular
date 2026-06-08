<?php

namespace Modules\Apps\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\Apps\Console\Commands\SyncApps;

class AppsServiceProvider extends ServiceProvider
{
    protected string $name = 'Apps';
    protected string $nameLower = 'apps';

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'apps');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->registerLivewireComponents();
        $this->commands([SyncApps::class]);
    }

    private function registerLivewireComponents(): void
    {
        $namespace = 'Modules\\Apps\\Livewire';
        $path = __DIR__ . '/../Livewire';

        if (! File::exists($path)) {
            return;
        }

        foreach (File::allFiles($path) as $component) {
            $relativePath = $component->getRelativePathname();
            $class = $namespace . '\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);
            $folder = strtolower(str_replace(['/', '\\'], '.', dirname($relativePath)));
            $aliasName = $this->kebabCase(pathinfo($relativePath, PATHINFO_FILENAME));
            $alias = ($folder && $folder !== '.') ? $folder . '.' . $aliasName : $aliasName;

            Livewire::component($alias, $class);
        }
    }

    private function kebabCase(string $string): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $string));
    }
}
