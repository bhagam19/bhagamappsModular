<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Modules\User\Entities\User;

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

                Gate::define('guest-only', fn($user = null) => $user === null);

                Gate::define('usuarios.user', function ($user) {
                        return $user instanceof User
                                && in_array($user->role->nombre, ['Administrador', 'Rector', 'Coordinador']);
                });

                // Define permisos para el módulo de inventario

                Gate::define('ver-bienes', function ($user) {
                        return $user->hasPermission('ver-bienes');
                });

                Gate::define('ver-actas-de-entrega', function ($user) {
                        return $user->hasPermission('ver-actas-de-entrega');
                });

                Gate::define('ver-ubicaciones', function ($user) {
                        return $user->hasPermission('ver-ubicaciones');
                });

                Gate::define('ver-dependencias', function ($user) {
                        return $user->hasPermission('ver-dependencias');
                });

                Gate::define('ver-categorias-bienes', function ($user) {
                        return $user->hasPermission('ver-categorias-bienes');
                });

                Gate::define('ver-estados', function ($user) {
                        return $user->hasPermission('ver-estados');
                });

                Gate::define('gestionar-historial-modificaciones-bienes', function ($user) {
                        return $user->hasPermission('gestionar-historial-modificaciones-bienes');
                });

                Gate::define('ver-historial-modificaciones', function ($user) {
                        return $user->hasPermission('ver-historial-modificaciones');
                });

                Gate::define('ver-historial-ubicaciones', function ($user) {
                        return $user->hasPermission('ver-historial-ubicaciones');
                });

                Gate::define('ver-responsables', function ($user) {
                        return $user->hasPermission('ver-responsables');
                });

                Gate::define('ver-mantenimientos-programados', function ($user) {
                        return $user->hasPermission('ver-mantenimientos-programados');
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

                Gate::define('aprobar-cambios-bienes', function ($user) {
                        return $user->hasPermission('aprobar-pendientes-bienes');
                });

                Gate::define('rechazar-cambios-bienes', function ($user) {
                        return $user->hasPermission('aprobar-pendientes-bienes');
                });

                Gate::define('administrar-apps', function ($user) {
                        return $user->hasPermission('administrar-apps');
                });

                Gate::define('ver-apps', function ($user) {
                        return $user->hasPermission('ver-apps');
                });

                Gate::define('crear-apps', function ($user) {
                        return $user->hasPermission('crear-apps');
                });

                Gate::define('editar-apps', function ($user) {
                        return $user->hasPermission('editar-apps');
                });

                Gate::define('eliminar-apps', function ($user) {
                        return $user->hasPermission('eliminar-apps');
                });

                // 📌 [crud-generator-gates] Añadir gates aquí
}
}
