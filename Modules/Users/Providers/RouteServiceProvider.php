<?php

namespace Modules\Users\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware('web')
                ->namespace('Modules\Users\Http\Controllers')
                ->group(base_path('Modules/Users/Routes/web.php'));
        });
    }
}