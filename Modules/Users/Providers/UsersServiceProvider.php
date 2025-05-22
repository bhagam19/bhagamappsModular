<?php

namespace Modules\Users\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use Livewire\Livewire;
use Modules\Users\Livewire\Users\UsersIndex;
use Modules\Users\Livewire\Users\EditarNombresUser;
use Modules\Users\Livewire\Users\EditarApellidosUser;
use Modules\Users\Livewire\Users\EditarEmailUser;
use Modules\Users\Livewire\Users\EditarUserIDUser;
use Modules\Users\Livewire\Users\EditarRolUser;

use Modules\Users\Livewire\Roles\RolesIndex;
use Modules\Users\Livewire\Roles\EditarNombreRole;
use Modules\Users\Livewire\Roles\EditarDescripcionRole;
use Modules\Users\Livewire\Roles\EditarRolePermissions;

use Modules\Users\Livewire\Permissions\PermissionsIndex;
use Modules\Users\Livewire\Permissions\EditarNombrePermission;
use Modules\Users\Livewire\Permissions\EditarDescripcionPermission;
use Modules\Users\Livewire\Permissions\EditarCategoriaPermission;

class UsersServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Users';

    protected string $nameLower = 'users';

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
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));

        $this->loadRoutesFrom(module_path($this->name, '/Routes/web.php'));

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        
        $this->loadViewsFrom(module_path('Users', 'Resources/views'), 'users');

        Livewire::component('users.users-index', UsersIndex::class);
        Livewire::component('users.editar-nombres-user', EditarNombresUser::class);
        Livewire::component('users.editar-apellidos-user', EditarApellidosUser::class);
        Livewire::component('users.editar-email-user', EditarEmailUser::class);
        Livewire::component('users.editar-userID-user', EditarUserIDUser::class);
        Livewire::component('users.editar-rol-user', EditarRolUser::class);

        Livewire::component('roles.roles-index', RolesIndex::class);
        Livewire::component('roles.editar-nombre-role', EditarNombreRole::class);
        Livewire::component('roles.editar-descripcion-role', EditarDescripcionRole::class);
        Livewire::component('roles.editar-role-permissions', EditarRolePermissions::class);

        Livewire::component('permissions.permissions-index', PermissionsIndex::class);
        Livewire::component('permissions.editar-nombre-permission', EditarNombrePermission::class);
        Livewire::component('permissions.editar-descripcion-permission', EditarDescripcionPermission::class);
        Livewire::component('permissions.editar-categoria-permission', EditarCategoriaPermission::class);

    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        //$this->app->register(EventServiceProvider::class);
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
