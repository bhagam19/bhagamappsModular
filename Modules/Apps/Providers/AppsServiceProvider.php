<?php

namespace Modules\Apps\Providers;

use Illuminate\Support\ServiceProvider;

class AppsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        // Aquí puedes registrar bindings o servicios
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'apps');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }
}
