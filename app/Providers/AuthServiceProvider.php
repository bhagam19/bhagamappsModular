<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
                        return $user->hasPermission('ver-usuarios');
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
                        return $user->hasPermission('ver-grupos');
                });

                Gate::define('admin.evaldoc', function ($user) {
                        return $user->hasPermission('ver-evaluacion-docente');
                });

                Gate::define('admin.biblioteca', function ($user) {
                        return $user->hasPermission('ver-biblioteca');
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

                // Gates para catálogos maestros de Inventario (IMPL-INV-002)
                foreach ([
                        'ver-categorias','crear-categorias','editar-categorias','eliminar-categorias',
                        'ver-dependencias','crear-dependencias','editar-dependencias','eliminar-dependencias',
                        'ver-ubicaciones','crear-ubicaciones','editar-ubicaciones','eliminar-ubicaciones',
                        'ver-estados','crear-estados','editar-estados','eliminar-estados',
                        'ver-origenes','crear-origenes','editar-origenes','eliminar-origenes',
                        'ver-almacenamientos','crear-almacenamientos','editar-almacenamientos','eliminar-almacenamientos',
                        'ver-mantenimientos','crear-mantenimientos','editar-mantenimientos','eliminar-mantenimientos',
                ] as $slug) {
                        Gate::define($slug, fn($user) => $user->hasPermission($slug));
                }

                // Gates para responsables y custodios de Inventario (IMPL-INV-003)
                foreach ([
                        'ver-responsables-bienes',
                        'asignar-responsables-bienes',
                        'editar-responsables-bienes',
                        'transferir-responsables-bienes',
                ] as $slug) {
                        Gate::define($slug, fn($user) => $user->hasPermission($slug));
                }

                // Gate para HEB — Historial Eliminaciones de Bienes (IMPL-INV-002A)
                Gate::define('gestionar-historial-eliminaciones-bienes', fn($user) => $user->hasPermission('gestionar-historial-eliminaciones-bienes'));

                // Gates para Historial de Ubicaciones (IMPL-INV-005)
                foreach ([
                        'ver-historial-ubicaciones-bienes',
                        'cambiar-ubicacion-bienes',
                ] as $slug) {
                        Gate::define($slug, fn($user) => $user->hasPermission($slug));
                }

                // 📌 [crud-generator-gates] Añadir gates aquí
}
}
