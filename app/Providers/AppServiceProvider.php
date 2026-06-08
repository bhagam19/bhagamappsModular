<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        setlocale(LC_ALL, 'es_CO.UTF-8');

        // Fix Livewire update URI for subdirectory deployment (/Modular)
        $base = trim(parse_url(config('app.url'), PHP_URL_PATH) ?? '', '/');
        if ($base) {
            Livewire::setUpdateRoute(function ($handle) use ($base) {
                return Route::post("{$base}/livewire/update", $handle)
                    ->middleware('web')
                    ->name('modular.livewire.update'); // ends with .livewire.update — passes Livewire::isLivewireRoute()
            });
        }
    }
}
