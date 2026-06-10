<?php

namespace App\Actions\Core;

use App\Auth\Capacidad;
use App\Auth\RolInstitucional;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Siembra (de forma idempotente) los permisos de core y los roles
 * institucionales fijos de APPSisGOE, y aplica la matriz rol×permiso.
 *
 * Conforme a ADR-0007 (modelo hibrido tras la fachada Gate):
 *   - Permisos de core: tomados de App\Auth\Capacidad::permisosCore()
 *     (fuente de verdad en codigo; RA-4). Guard `web` (RA-7, sin teams).
 *   - Roles institucionales fijos: App\Auth\RolInstitucional (RA-6).
 *   - NO crea rol Spatie `super-admin`: el SuperAdministrador es eje tecnico
 *     (RolSistema) con bypass via Gate::before (RA-2 + conciliacion P5).
 *   - Idempotente: findOrCreate + syncPermissions no duplican filas; puede
 *     re-ejecutarse sin efectos adversos.
 *
 * Esta es la UNICA capa (junto al seeder que delega aqui) autorizada a usar la
 * API propietaria de Spatie. Controladores, FormRequests y Blade consultan
 * exclusivamente la fachada Gate (RA-3).
 *
 * No depende de HTTP (no usa request()/auth()); reutilizable desde seeder,
 * comando o test (paradigma POO, regla 8).
 */
class SincronizarRolesYPermisosCoreAction
{
    /**
     * Guard unico del sistema (config/auth.php). Todos los roles y permisos
     * institucionales se crean con este guard (ADR-0007: guard_name = web).
     */
    private const GUARD = 'web';

    public function execute(): void
    {
        $this->sincronizarPermisos();
        $this->sincronizarRoles();

        // Conforme a RA-9: limpiar la cache de permisos tras sembrar.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Crea cada permiso de core si no existe (idempotente).
     */
    private function sincronizarPermisos(): void
    {
        foreach (Capacidad::permisosCore() as $capacidad) {
            Permission::findOrCreate($capacidad->value, self::GUARD);
        }
    }

    /**
     * Crea cada rol institucional fijo si no existe y sincroniza sus permisos
     * segun la matriz (idempotente: syncPermissions reemplaza el conjunto).
     */
    private function sincronizarRoles(): void
    {
        foreach ($this->matrizRolPermiso() as $valorRol => $permisos) {
            $rol = Role::findOrCreate($valorRol, self::GUARD);

            $rol->syncPermissions(
                array_map(static fn (Capacidad $c): string => $c->value, $permisos)
            );
        }
    }

    /**
     * Matriz rol×permiso del core, conforme a
     * docs/fase-1-core/especificacion-tecnica/permisos-y-roles-core.md.
     *
     * El SuperAdministrador NO aparece: accede por bypass Gate::before.
     *
     * @return array<string, list<Capacidad>>
     */
    private function matrizRolPermiso(): array
    {
        return [
            // Administrador institucional: todos los permisos de core.
            RolInstitucional::Admin->value => Capacidad::permisosCore(),

            // Rectoria: visibilidad general + reportes + impresion; sin
            // administracion tecnica ni gestion de modulos.
            RolInstitucional::Rectoria->value => [
                Capacidad::ConfiguracionVer,
                Capacidad::UsuariosVer,
                Capacidad::RolesVer,
                Capacidad::ModulosVer,
                Capacidad::SaludVer,
                Capacidad::PlantillasVer,
                Capacidad::ImpresionImprimir,
                Capacidad::ImpresionReimprimir,
                Capacidad::ImpresionVer,
                Capacidad::AuditoriaVer,
                Capacidad::OperacionesVer,
                Capacidad::RestauracionVer,
                Capacidad::ActualizacionesVer,
                Capacidad::ComunidadMiembrosVer,
                Capacidad::InventarioBienesVer,
                Capacidad::InventarioResponsablesVer,
                Capacidad::InventarioResponsablesTransferir,
            ],

            // Coordinacion: acceso a modulos y consultas operativas.
            RolInstitucional::Coordinacion->value => [
                Capacidad::UsuariosVer,
                Capacidad::ModulosVer,
                Capacidad::SaludVer,
                Capacidad::PlantillasVer,
                Capacidad::ImpresionImprimir,
                Capacidad::ImpresionVer,
                Capacidad::ComunidadMiembrosVer,
                Capacidad::ComunidadMiembrosCrear,
                Capacidad::ComunidadMiembrosEditar,
                Capacidad::InventarioCategoriasVer,
                Capacidad::InventarioUbicacionesVer,
                Capacidad::InventarioBienesVer,
                Capacidad::InventarioResponsablesVer,
                Capacidad::InventarioResponsablesAsignar,
                Capacidad::InventarioResponsablesEditar,
                Capacidad::InventarioResponsablesTransferir,
            ],

            // Docente: solo imprime.
            RolInstitucional::Docente->value => [
                Capacidad::ImpresionImprimir,
            ],

            // Consulta: solo lectura/impresion.
            RolInstitucional::Consulta->value => [
                Capacidad::ImpresionImprimir,
            ],
        ];
    }
}
