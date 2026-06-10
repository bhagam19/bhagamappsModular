<?php

namespace App\Auth;

/**
 * Enum central de capacidades (permisos atomicos del sistema).
 *
 * Conforme a ADR-0002 (docs/decisiones/0002-autorizacion.md), regla R9, y a
 * ADR-0007 (docs/decisiones/0007-autorizacion-roles-permisos.md), regla RA-4:
 * los strings de capacidades/permisos NO pueden usarse como literales sueltos
 * en controladores ni en Blade; siempre vienen de este enum. Backed enum
 * string para usarlo directamente en @can(), authorize() y para sembrar la
 * tabla `permissions` de Spatie (fuente de verdad en codigo del catalogo de
 * core; los modulos declaran los suyos en su manifiesto, BT-004+).
 *
 * Dos clases de capacidad conviven aqui (ejes ortogonales de ADR-0007):
 *   - Capacidad TECNICA de plataforma: AdministrarSistema. Pertenece al eje
 *     RolSistema (super_administrador) y se resuelve por Gate::before. NO se
 *     siembra como permiso institucional Spatie.
 *   - Permisos INSTITUCIONALES de core (recurso:accion): se siembran en Spatie
 *     y se asignan a los roles institucionales fijos. Ver permisosCore().
 *
 * Convencion unica de nombres (conciliacion AUD-003 / BT-003): `recurso:accion`
 * en minusculas, segun el catalogo canonico de
 * docs/fase-1-core/especificacion-tecnica/permisos-y-roles-core.md.
 */
enum Capacidad: string
{
    // ─── Capacidad tecnica de plataforma (eje RolSistema; bypass Gate::before) ───
    case AdministrarSistema = 'administrar_sistema';

    // ─── Configuracion institucional ───
    case ConfiguracionVer = 'configuracion:ver';
    case ConfiguracionAdministrar = 'configuracion:administrar';

    // ─── Usuarios ───
    case UsuariosVer = 'usuarios:ver';
    case UsuariosCrear = 'usuarios:crear';
    case UsuariosActualizar = 'usuarios:actualizar';
    case UsuariosEliminar = 'usuarios:eliminar';

    // ─── Roles ───
    case RolesVer = 'roles:ver';
    case RolesAdministrar = 'roles:administrar';

    // ─── Modulos ───
    case ModulosVer = 'modulos:ver';
    case ModulosInstalar = 'modulos:instalar';
    case ModulosActualizar = 'modulos:actualizar';
    case ModulosAdministrar = 'modulos:administrar';
    case ModulosRestaurar = 'modulos:restaurar';

    // ─── Salud del sistema ───
    case SaludVer = 'salud:ver';

    // ─── Plantillas documentales ───
    case PlantillasVer = 'plantillas:ver';
    case PlantillasAdministrar = 'plantillas:administrar';

    // ─── Impresion ───
    case ImpresionImprimir = 'impresion:imprimir';
    case ImpresionReimprimir = 'impresion:reimprimir';
    case ImpresionVer = 'impresion:ver';

    // ─── Auditoria ───
    case AuditoriaVer = 'auditoria:ver';

    // ─── Operaciones del sistema ───
    case OperacionesVer = 'operaciones:ver';
    case OperacionesAdministrar = 'operaciones:administrar';

    // ─── Restauracion ───
    case RestauracionVer = 'restauracion:ver';
    case RestauracionEjecutar = 'restauracion:ejecutar';
    case RestauracionAdministrar = 'restauracion:administrar';

    // ─── Actualizaciones ───
    case ActualizacionesVer = 'actualizaciones:ver';
    case ActualizacionesEjecutar = 'actualizaciones:ejecutar';

    // ─── Estructura institucional ───
    case EstructuraInstitucionalVer = 'estructura_institucional:ver';
    case EstructuraInstitucionalAdministrar = 'estructura_institucional:administrar';

    // ─── Planeación institucional ───
    case PlaneacionInstitucionalVer = 'planeacion_institucional:ver';
    case PlaneacionInstitucionalAdministrar = 'planeacion_institucional:administrar';

    // ─── Inventario institucional ───
    case InventarioCategoriasVer    = 'inventario_categorias:ver';
    case InventarioCategoriasCrear  = 'inventario_categorias:crear';
    case InventarioCategoriasEditar = 'inventario_categorias:editar';

    case InventarioUbicacionesVer    = 'inventario_ubicaciones:ver';
    case InventarioUbicacionesCrear  = 'inventario_ubicaciones:crear';
    case InventarioUbicacionesEditar = 'inventario_ubicaciones:editar';

    case InventarioBienesVer    = 'inventario_bienes:ver';
    case InventarioBienesCrear  = 'inventario_bienes:crear';
    case InventarioBienesEditar = 'inventario_bienes:editar';

    case InventarioResponsablesVer        = 'inventario_responsables:ver';
    case InventarioResponsablesAsignar    = 'inventario_responsables:asignar';
    case InventarioResponsablesEditar     = 'inventario_responsables:editar';
    case InventarioResponsablesTransferir = 'inventario_responsables:transferir';

    // ─── Comunidad educativa ───
    case ComunidadMiembrosVer = 'comunidad_miembros:ver';
    case ComunidadMiembrosCrear = 'comunidad_miembros:crear';
    case ComunidadMiembrosEditar = 'comunidad_miembros:editar';

    /**
     * ¿Es una capacidad tecnica de plataforma (eje RolSistema)?
     *
     * Las capacidades tecnicas NO se siembran como permisos institucionales
     * Spatie: las resuelve Gate::before para el SuperAdministrador.
     */
    public function esTecnica(): bool
    {
        return $this === self::AdministrarSistema;
    }

    /**
     * Permisos INSTITUCIONALES de core que se siembran en Spatie y se asignan
     * a los roles institucionales fijos. Excluye las capacidades tecnicas.
     *
     * @return list<self>
     */
    public static function permisosCore(): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn (self $capacidad): bool => ! $capacidad->esTecnica(),
        ));
    }
}
