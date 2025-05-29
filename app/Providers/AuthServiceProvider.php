<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\Users\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('admin.users', function ($user) {        
        return $user instanceof User
                && in_array($user->role->nombre, ['Administrador', 'Rector', 'Coordinador']);
        });

        Gate::define('admin.grupos', function ($user) {        
        return $user instanceof User
                && in_array($user->role->nombre, ['Administrador', 'Rector', 'Coordinador']);
        });

        Gate::define('admin.evaldoc', function ($user) {        
        return $user instanceof User
                && in_array($user->role->nombre, ['Administrador', 'Rector', 'Coordinador']);
        });
        Gate::define('admin.biblioteca', function ($user) {        
        return $user instanceof User
                && in_array($user->role->nombre, ['Administrador', 'Rector', 'Coordinador']);
        });
    }
}
