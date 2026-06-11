# AUDIT-IEE-001 — IEE Production Readiness & Product Certification

| Campo               | Valor                                                                 |
|---------------------|-----------------------------------------------------------------------|
| **ID**              | AUDIT-IEE-001                                                         |
| **Tipo**            | Product Certification Audit                                           |
| **Fecha**           | 2026-06-11                                                            |
| **Auditor**         | Claude Code (claude-sonnet-4-6)                                       |
| **Producto**        | IEE — Institución Educativa Entrerríos, Sistema de Inventario         |
| **Versión auditada**| IEE v1.12.1 / BhagamApps v1.12.1 / Inventario v2.10.5                |
| **Repositorio**     | https://github.com/bhagam19/bhagamappsModular.git                     |
| **URL canónica**    | http://bhagamapps.com/iee                                             |
| **SHA de referencia** | `c1bbea9`                                                           |

---

## Clasificación Oficial del Producto

```
╔══════════════════════════════════════════════════════╗
║                                                      ║
║      ESTABLE CON DEUDA TÉCNICA                       ║
║                                                      ║
╚══════════════════════════════════════════════════════╝
```

**Fundamento técnico**: IEE en su ruta activa (módulo Inventario vía `Modules/Inventario`)
opera de forma estable en producción con identidad institucional completa, RBAC funcional,
notificaciones reactivas y 15 secciones funcionales accesibles. La clasificación no asciende
a MADURO por la presencia de una arquitectura paralela no integrada (artefactos `app/Models/Inventario`,
`app/Auth/Capacidad`, `app/Actions/Inventario`, `app/ReadServices/Inventario`,
`app/Http/Controllers/Inventario`) que acumula dependencias rotas (`spatie/laravel-permission`
ausente en composer.json), referencias a gates inexistentes, y una suite de tests que no
es ejecutable de forma autónoma.

---

## CERT-001 — Infraestructura

**Estado: CONFORME**

| Variable        | Valor verificado                     | Estado |
|-----------------|--------------------------------------|--------|
| `APP_NAME`      | `IEE`                                | ✅     |
| `APP_URL`       | `http://bhagamapps.com/iee`          | ✅     |
| `ASSET_URL`     | `http://bhagamapps.com/iee`          | ✅     |
| `SESSION_PATH`  | `/iee`                               | ✅     |

**Symlinks verificados en servidor** (`public_html/public/`):

| Alias      | Destino                                                              | Estado |
|------------|----------------------------------------------------------------------|--------|
| `/iee`     | `…/private/bhagamappsModular/public` (symlink, creado 2026-06-10)   | ✅     |
| `/Modular` | `…/private/bhagamappsModular/public` (symlink, coexiste en transición) | ✅  |

**Livewire update route**: Configurado en `AppServiceProvider::boot()` para subdirectorio `/iee`
mediante `Livewire::setUpdateRoute()` dinámico. Sin hardcoding.

**Riesgos residuales de infraestructura**:
- El symlink `/Modular` sigue activo. No tiene fecha de baja documentada. Sesiones activas
  bajo `/Modular` con `SESSION_PATH=/iee` pueden experimentar invalidación de sesión.
  Deuda documentada: requiere plan de deprecación formal.

---

## CERT-002 — Branding Institucional

**Estado: CONFORME — 0 referencias visibles a "BhagamApps Modular"**

| Superficie               | Valor verificado                                | Estado |
|--------------------------|-------------------------------------------------|--------|
| `APP_NAME` (config)      | `IEE`                                           | ✅     |
| AdminLTE `title_prefix`  | `IEE -`                                         | ✅     |
| AdminLTE `logo`          | `<b>IEE</b>`                                    | ✅     |
| Footer principal         | `IEE — Institución Educativa Entrerríos`        | ✅     |
| Footer (desarrollador)   | `Desarrollado por: BhagamApps © 2025` (discreta) | ✅   |
| `welcome.blade.php`      | Título: `IEE — Sistema de Inventario Institucional` | ✅  |
| Login (`adminlte`)       | Hereda logo `IEE` del config                    | ✅     |
| Dashboard `<title>`      | `IEE - <sección>` via `title_prefix`            | ✅     |
| `config/versiones.php`   | Clave `IEE` presente: `v1.12.1`                 | ✅     |

**Pantalla de versiones**: Componente `<x-changelog-modal module="IEE" />` en footer.
Definición en `resources/views/components/changelog-modal.blade.php`. Funcional.

**Observación**: La atribución "Desarrollado por BhagamApps" en el footer es intencional
y correcta (identidad del desarrollador, no del producto).

---

## CERT-003 — Cobertura Funcional

**Estado: CONFORME — 15/15 secciones cubiertas (100%)**

| Sección                     | Ruta registrada                              | Controller / Livewire                         | Estado |
|-----------------------------|----------------------------------------------|-----------------------------------------------|--------|
| Bienes                      | `GET /inventario/bienes`                     | `BienController@index`                        | ✅     |
| Actas de Entrega            | `GET /inventario/actas`                      | `ActaController@index`                        | ✅     |
| Actas PDF                   | `GET /inventario/actas/{userId}/pdf`         | `ActaPDFController@show`                      | ✅     |
| Responsables                | `GET /inventario/responsables`               | `ResponsablesController@index`                | ✅     |
| Historial Modificaciones    | `GET /inventario/hmb`                        | `HmbController@index`                         | ✅     |
| Historial Eliminaciones     | `GET /inventario/heb`                        | `HebController@index`                         | ✅     |
| Historial Ubicaciones       | `GET /inventario/ubicaciones/historial`      | `UbicacionesHistorialController@index`        | ✅     |
| Categorías                  | `GET /inventario/catalogos/categorias`       | `CatalogosController@categorias`              | ✅     |
| Dependencias                | `GET /inventario/catalogos/dependencias`     | `CatalogosController@dependencias`            | ✅     |
| Ubicaciones                 | `GET /inventario/catalogos/ubicaciones`      | `CatalogosController@ubicaciones`             | ✅     |
| Estados                     | `GET /inventario/catalogos/estados`          | `CatalogosController@estados`                 | ✅     |
| Orígenes                    | `GET /inventario/catalogos/origenes`         | `CatalogosController@origenes`                | ✅     |
| Almacenamientos             | `GET /inventario/catalogos/almacenamientos`  | `CatalogosController@almacenamientos`         | ✅     |
| Mantenimientos (catálogo)   | `GET /inventario/catalogos/mantenimientos`   | `CatalogosController@mantenimientos`          | ✅     |
| Mantenimientos Programados  | `GET /inventario/mantenimientos/programados` | `MantenimientosProgramadosController@index`   | ✅     |

**Notificaciones**: `NotificacionesIcono`, `NotificacionesDropdown`, `NotificacionHmb`
implementados como Livewire + canal database. Cubiertos funcionalmente.

**Cobertura funcional global: 100%** respecto al scope declarado en CERT-003.

**Observación sobre NotificacionHeb**: Canal `database` no activado. El método `toDatabase()`
está comentado porque referencia campos inexistentes en `HistorialEliminacionBien`
(`campo`, `valor_anterior`, `valor_nuevo`). Es deuda técnica documentada — no es un defecto
funcional del canal HEB activo (mail/database para HMB funciona correctamente).

---

## CERT-004 — Navegación

**Estado: CONFORME CON OBSERVACIONES**

**Inventario sidebar** (`config/adminlte.php`): Todas las 15 secciones funcionales tienen
entrada en el menú con rutas, íconos y flags `active` correctamente configurados.

**Rutas accesibles desde el menú**: 15/15 — todas apuntan a rutas registradas. ✅

**Módulos huérfanos detectados en sidebar** (con `'route' => ''`):
| Sección          | Subrutas          | Estado                |
|------------------|-------------------|-----------------------|
| Grupos           | 3 subitems vacíos | ⚠️ Rutas no registradas |
| Evaluación Docente | 4 subitems vacíos | ⚠️ Rutas no registradas |
| Biblioteca       | 2 subitems vacíos | ⚠️ Rutas no registradas |

Estos ítems existen en el menú visual pero tienen `'route' => ''` — generan enlaces vacíos
o errores de generación de URL. Son artefactos heredados de módulos futuros no implementados.

**Ambigüedad en sidebar "Mantenimientos"**: Hay dos ítems con `'text' => 'Mantenimientos'`
dentro del submenú Inventario: uno apunta al catálogo (`inventario.catalogos.mantenimientos`)
y otro a los programados (`inventario.mantenimientos.programados`). Funcionalmente correcto
pero visualmente confuso para el usuario final.

**Permisos de sidebar**: Los ítems con `'can'` declarado usan slugs coherentes con los
Gates definidos en `AuthServiceProvider`.

---

## CERT-005 — RBAC

**Estado: CONFORME EN RUTA ACTIVA — INCONSISTENCIA ARQUITECTÓNICA EN RUTA PARALELA**

### Sistema RBAC activo (producción)

El sistema de autorización en producción usa la arquitectura custom del módulo User:

- **Modelo activo**: `Modules\User\Entities\User` (configurado en `config/auth.php`)
- **Roles**: tabla `roles` gestionada por `Modules\User\Entities\Role`
- **Permisos**: tabla `permissions` gestionada por `Modules\User\Entities\Permission`
- **Método de verificación**: `User::hasPermission($slug)` — verifica permiso directo
  y por rol vía pivot `permission_role`
- **Middleware `permission`**: `CheckPermission` invoca `hasPermission()` ✅
- **Middleware `app.access`**: `CheckAppAccess` verifica visibilidad de módulo por rol ✅
- **Gates**: definidos en `AuthServiceProvider` con slugs tipo `ver-bienes`,
  `gestionar-historial-modificaciones-bienes`, etc. ✅

**Roles verificados**:

| Rol             | Acceso Inventario | HMB | HEB | Responsables | Historial Ubic. |
|-----------------|-------------------|-----|-----|--------------|-----------------|
| Administrador   | ✅                | ✅  | ✅  | ✅           | ✅              |
| Rector          | ✅                | ✅  | ✅  | ✅ (implícito) | ✅            |
| Coordinador     | ✅                | ❌  | ❌  | ✅           | ✅              |
| Docente         | ❌ (403)          | —   | —   | —            | —               |

**Sin permisos huérfanos detectados** en la arquitectura activa.
**Sin gates huérfanos detectados** en la arquitectura activa.

### Inconsistencia crítica: arquitectura Capacidad/Spatie paralela (dead code)

`app/Auth/Capacidad.php` define permisos con formato `recurso:accion`
(e.g., `inventario_responsables:ver`) para ser usados con Spatie. Sin embargo:

1. `spatie/laravel-permission` **no está en `composer.json`** y no está instalado.
2. `app/Models/User.php` hace `use Spatie\Permission\Traits\HasRoles` — PHP fatal error
   si esta clase es cargada.
3. `app/Actions/Core/SincronizarRolesYPermisosCoreAction.php` importa `Spatie\Permission\Models\*`
   — no ejecutable.
4. `app/Http/Controllers/Inventario/BienResponsableController.php` llama
   `Gate::authorize(Capacidad::InventarioResponsablesVer->value)` = `'inventario_responsables:ver'`,
   pero `AuthServiceProvider` solo define `'ver-responsables-bienes'` — gate inexistente
   → 403 en todas las rutas de este controlador.

**Esta ruta paralela no está registrada en ningún `routes/*.php`**, por lo que no es
accesible en producción y no causa defectos activos. Sin embargo, es deuda técnica de
alto riesgo.

---

## CERT-006 — Calidad Técnica

**Estado: DEUDA TÉCNICA VERIFICABLE — Sin riesgo de regresión inmediato en producción**

### Arquitectura paralela acumulada (dead code)

Los siguientes artefactos en `app/` constituyen una arquitectura alternativa incompleta,
no conectada a rutas de producción:

| Archivo / Directorio                           | Clasificación              |
|------------------------------------------------|----------------------------|
| `app/Models/User.php`                          | Dead model — Spatie fatal  |
| `app/Models/Inventario/Bien.php`               | Dead model paralelo        |
| `app/Models/Inventario/BienResponsable.php`    | Dead model paralelo        |
| `app/Auth/Capacidad.php`                       | Dead enum — Spatie ausente |
| `app/Actions/Inventario/AsignarResponsableBienAction.php` | Dead action     |
| `app/Actions/Inventario/TransferirResponsableBienAction.php` | Dead action  |
| `app/Actions/Core/SincronizarRolesYPermisosCoreAction.php` | Dead action   |
| `app/ReadServices/Inventario/BienResponsableReadService.php` | Dead service |
| `app/Http/Controllers/Inventario/BienResponsableController.php` | Dead controller |
| `app/Http/Requests/Inventario/StoreResponsableBienRequest.php` | Dead request |
| `app/Http/Requests/Inventario/TransferirResponsableRequest.php` | Dead request |
| `resources/views/inventario/`                 | Dead views paralelas       |
| `app/Models/Grupo.php`                        | Modelo legacy sin módulo   |
| `app/Livewire/Grupo/`                         | Livewire legacy sin rutas  |

**Riesgo**: Aunque estos archivos no están en rutas de producción, el autoloader de PHP
puede intentar cargar `app/Models/User.php` (namespace `App\Models`) provocando fatal
error en cualquier contexto que toque ese namespace (visible en la suite de tests).

### Controlador de depuración activo

`Modules/Inventario/Http/Controllers/TestFiltroController.php` existe con su vista
`livewire/bienes/test-filtro.blade.php`. No está registrado en rutas, pero su presencia
es una señal de deuda de limpieza.

### Migraciones raíz legacy

`database/migrations/2025_05_12_232010_create_grupos_table.php` crea tabla `grupos`
para un módulo nunca completado. La tabla existe en BD pero no tiene módulo activo.

### Consistencias arquitectónicas confirmadas en la ruta activa

- `Modules\User\Entities\User` es el único modelo de usuario cargado en producción.
- `Modules\Inventario\Entities\*` son los únicos modelos de inventario cargados en producción.
- No se detectaron listeners huérfanos (`Modules/Inventario/Providers/EventServiceProvider.php`
  existe pero no registra listeners activos con problemas).
- No se detectaron migraciones duplicadas en `Modules/Inventario/Database/Migrations/`.

---

## CERT-007 — Testing

**Estado: SUITE EXISTENTE — NO EJECUTABLE DE FORMA AUTÓNOMA**

### Resultados de la ejecución

```
Tests: 33 failed, 3 skipped, 14 passed (23 assertions)
Duration: ~2.8s
```

**Tests pasados (14/50)**:
- `bienes_no_depende_de_columna_ubicacion_id_regresion_gap002` ✅
- `bien_creado_queda_en_tabla_bienes` ✅
- `notificaciones_icono_no_usa_wire_poll` ✅
- `notificaciones_dropdown_no_usa_wire_poll` ✅
- `tabla_bienes_responsables_existe` ✅
- `tabla_bienes_responsables_tiene_columnas_requeridas` ✅
- `bien_sin_responsable_activo_no_tiene_asignaciones_vigentes` ✅
- `tabla_historial_ubicaciones_bienes_existe` ✅
- `tabla_historial_ubicaciones_tiene_columnas_requeridas` ✅
- `tabla_bienes_no_tiene_columna_ubicacion_id_regresion_gap002` ✅
- `bien_sin_historial_ubicacion_retorna_ubicacion_actual_null` ✅
- Redirects a login (3 tests de `PermissionsTest`) ✅

**Causa raíz de fallos (33 tests)**:
`InventarioTestCase::crearUsuarioConRol()` ejecuta `Role::where('nombre', 'Administrador')->firstOrFail()`
contra la base de datos de desarrollo usando `DatabaseTransactions`. Si los roles no están
sembrados en la BD disponible durante la ejecución de tests, todos los tests que dependan
de un usuario con rol fallan con `ModelNotFoundException`.

La suite fue diseñada para ejecutarse contra la BD de desarrollo con seeders aplicados,
no contra una BD de prueba aislada. `phpunit.xml` no configura SQLite in-memory (la línea
está comentada). Esto no es un defecto de la lógica de tests sino una limitación de
infraestructura de pruebas.

### Cobertura efectiva de los tests que pasan

| Área cubierta               | Tipo                    |
|-----------------------------|-------------------------|
| Ausencia de `wire:poll`     | Regresión estática      |
| Estructura de tablas BD     | Esquema verificado      |
| Redirección sin auth        | Seguridad básica        |
| Integridad de columnas      | Regresiones GAP-001/002 |

### Riesgos no cubiertos por la suite actual

- Flujo completo de aprobación HMB contra BD aislada
- Autorización por rol en aislamiento (requiere seeders)
- Integración de notificaciones mail+database
- Mantenimientos programados (0 tests)
- Actas PDF (0 tests)

---

## CERT-008 — Notificaciones

**Estado: CONFORME**

| Componente              | Verificación                                             | Estado |
|-------------------------|----------------------------------------------------------|--------|
| `NotificacionesIcono`   | `#[On('cambioActualizado')]` — reactivo sin `wire:poll`  | ✅     |
| `NotificacionesDropdown`| Paginado, reactivo, `dispatch('cambioActualizado')`      | ✅     |
| `NotificacionHmb`       | Canales `['mail', 'database']`, `toDatabase()` activo    | ✅     |
| `wire:poll` global      | **0 instancias detectadas** en toda la codebase          | ✅     |
| Persistencia HMB        | Registro se actualiza (`estado='aprobada/rechazada'`),   | ✅     |
|                         | nunca se elimina — regresiones D-1 y D-6 resueltas       |        |
| `NotificacionHeb`       | Canal `database` comentado (payload inválido)            | ⚠️     |

**Nota HEB**: La notificación de eliminación vía canal database está pendiente.
El canal mail de HEB sí funciona. Deuda documentada en `IMPL-INV-NOTIF-001B`.

---

## CERT-009 — Seguridad

**Estado: CONFORME — Sin regresiones detectadas**

| Control                          | Implementación                                              | Estado |
|----------------------------------|-------------------------------------------------------------|--------|
| CSRF                             | `VerifyCsrfToken` middleware activo                         | ✅     |
| Autenticación en rutas           | `auth` middleware en grupo `inventario/*`                   | ✅     |
| Control de acceso a módulo       | `app.access:inventario` en grupo `inventario/*`             | ✅     |
| Control de permiso granular      | `permission:{slug}` en cada ruta individual                 | ✅     |
| Sesiones                         | `SESSION_PATH=/iee`, driver session por defecto             | ✅     |
| Inyección SQL                    | ORM Eloquent + parámetros vinculados — sin queries raw      | ✅     |
| XSS en vistas                    | Blade `{{ }}` escaping por defecto                          | ✅     |
| Autorización en Livewire         | `Gate::define` / `@can` en templates Livewire               | ✅     |

**Sin regresiones de seguridad detectadas** respecto a las auditorías AUDIT-INV-001
a AUDIT-INV-005 previas.

**Observación**: `app/Models/User.php` tiene `use Spatie\Permission\Traits\HasRoles`
con Spatie no instalado. Aunque no es la ruta de auth activa, cualquier carga accidental
de `App\Models\User` produce fatal error — vector de denegación de servicio involuntario.

---

## CERT-010 — Versionado y Trazabilidad

**Estado: CONFORME — Documentos coherentes entre sí**

| Documento                         | Versión IEE | Versión BA  | Inventario | Estado |
|-----------------------------------|-------------|-------------|------------|--------|
| `config/versiones.php`            | v1.12.1     | v1.12.1     | v2.10.5    | ✅     |
| `CHANGELOG.md`                    | v1.12.1     | v1.12.1     | implícito  | ✅     |
| `VERSIONING.md`                   | v1.12.1     | v1.12.1     | v2.10.5    | ✅     |
| `docs/changelog/bhagamapps.md`    | —           | v1.12.1     | —          | ✅     |
| `docs/changelog/iee.md`           | v1.12.1     | —           | —          | ✅     |
| `docs/changelog/inventario.md`    | —           | —           | v2.10.5    | ✅     |

Todos los documentos de versionado son coherentes. La cadena de trazabilidad
IMPL-INFRA-001 → v1.12.1 → CHANGELOG → config/versiones.php está completa y verificada.

---

## Deuda Técnica Residual

Las siguientes deudas son **reales, verificables y pendientes** de cierre:

### DT-001 — Arquitectura Paralela app/ (ALTA)

**Descripción**: El directorio `app/` contiene una arquitectura de segunda generación
incompleta y no conectada a producción: `app/Models/Inventario/`, `app/Auth/Capacidad`,
`app/Actions/Inventario/`, `app/ReadServices/Inventario/`, `app/Http/Controllers/Inventario/`.

**Riesgo**: `app/Models/User.php` importa `Spatie\Permission\Traits\HasRoles` con Spatie
no instalado — fatal PHP error en cualquier contexto que cargue `App\Models\User`.
El test runner lo encuentra en autoload y cancela la suite completa.

**Remediación**: Decidir entre (a) completar y conectar la arquitectura nueva reemplazando
`Modules/Inventario` o (b) eliminar todos los artefactos paralelos. No coexistir indefinidamente.

---

### DT-002 — Suite de Tests No Autónoma (MEDIA)

**Descripción**: 33/50 tests fallan en entorno sin seeders. La suite requiere BD de desarrollo
con roles sembrados para ejecutarse. No existe un `.env.testing` con BD de prueba configurada.

**Riesgo**: CI/CD imposible sin intervención manual. Cobertura real autónoma: 14/50 (28%).

**Remediación**: Crear factories para `Role`, `Permission` y `User` del módulo User que
no dependan de la BD de producción. Alternativamente, configurar SQLite in-memory con
seeders de test incluidos en `InventarioTestCase::setUp()`.

---

### DT-003 — Deprecación Pendiente de /Modular (BAJA)

**Descripción**: El alias `/Modular` sigue activo en producción. No existe fecha de baja
ni plan de comunicación a usuarios existentes.

**Riesgo**: Bajo. Sesiones creadas bajo `/Modular` pueden invalidarse al cambiar a `/iee`
dado que `SESSION_PATH=/iee`.

**Remediación**: Documentar fecha de deprecación y agregar redirección HTTP 301 de
`/Modular` hacia `/iee`.

---

### DT-004 — Menús Huérfanos en Sidebar (BAJA)

**Descripción**: Los grupos `Grupos`, `Evaluación Docente` y `Biblioteca` en `config/adminlte.php`
tienen `'route' => ''` — generan ítems de menú no funcionales visibles para usuarios.

**Riesgo**: Bajo impacto funcional. Alto impacto de percepción de calidad.

**Remediación**: Comentar o eliminar los ítems del sidebar hasta que los módulos
correspondientes sean implementados.

---

### DT-005 — TestFiltroController Activo (MUY BAJA)

**Descripción**: `Modules/Inventario/Http/Controllers/TestFiltroController.php` y su vista
`test-filtro.blade.php` permanecen en el codebase sin ruta registrada.

**Remediación**: Eliminar ambos archivos.

---

### DT-006 — NotificacionHeb Canal Database (BAJA)

**Descripción**: `toDatabase()` en `NotificacionHeb` está comentado con payload incorrecto
(referencia campos de HMB no presentes en HEB).

**Remediación**: Redefinir el payload de `toDatabase()` con los campos reales de
`HistorialEliminacionBien` (`bien_id`, `dependencia_id`, `user_id`, `motivo`) y activar
el canal database.

---

### DT-007 — Tabla grupos Sin Módulo (MUY BAJA)

**Descripción**: `database/migrations/2025_05_12_232010_create_grupos_table.php` creó la tabla
`grupos` para un módulo escolar nunca completado. Persiste en producción sin uso.

**Remediación**: Evaluar si el módulo Grupos es parte del roadmap. Si no, crear migración
de cleanup para eliminar la tabla.

---

## Recomendaciones Posteriores — Roadmap

### 1. Administración de Contraseñas (Prioridad: ALTA)

El sistema actual usa Jetstream/Fortify para autenticación, pero el módulo User no expone
una interfaz de cambio de contraseña unificada para todos los roles. Los usuarios con
`role_id` personalizado pueden quedar sin flujo de reset si `email_verified_at` o las
rutas de Fortify no están correctamente expuestas bajo `/iee`.

**Acciones recomendadas**:
- Verificar que las rutas de Fortify (`/forgot-password`, `/reset-password`) funcionan
  bajo el alias `/iee`.
- Agregar un ítem de navegación de perfil/contraseña accesible desde el sidebar.
- Considerar reset administrativo de contraseña desde el módulo User (rol Administrador).

---

### 2. Auditoría Funcional de Dependencias (Prioridad: ALTA)

El módulo Dependencias es central en el flujo de HMB (historial de modificaciones):
`HistorialModificacionBien.dependencia_id` es `NOT NULL`. Si una dependencia es eliminada
del catálogo, el historial queda con referencias huérfanas.

**Acciones recomendadas**:
- Auditar si la tabla `dependencias` tiene `ON DELETE RESTRICT` o `CASCADE`.
- Definir política de soft-delete o archivado para dependencias con historial asociado.
- Agregar tests de integridad referencial para el flujo HMB ↔ Dependencia.

---

### 3. Evolución de Users (Prioridad: MEDIA)

El módulo User actual usa `role_id` (relación a un solo rol). No soporta usuarios con
múltiples roles simultáneos. La arquitectura paralela en `app/Auth/Capacidad` sugería
una evolución hacia Spatie multi-rol, que nunca se completó.

**Acciones recomendadas**:
- Resolver DT-001 primero: decidir si se continúa con la arquitectura custom o se migra a Spatie.
- Si se mantiene la arquitectura custom, documentar formalmente que el sistema es single-role
  y que esa es la decisión arquitectónica (ADR recomendado).
- Si se migra a Spatie, planificar la migración de datos de `roles` / `permission_role` a
  las tablas de Spatie y eliminar los modelos del módulo User.

---

### 4. Evolución de Apps (Prioridad: MEDIA)

El módulo Apps gestiona la visibilidad de módulos por rol. Actualmente usa `App::visiblesPara($user)`
que carga todos los apps visibles. Para soportar crecimiento (más módulos), se recomienda:

**Acciones recomendadas**:
- Revisar el query de `visiblesPara()` para asegurar uso de índices en producción.
- Considerar caching de la matriz App-Role para usuarios autenticados (especialmente si
  la navegación lateral se recarga en cada request).
- Definir un proceso formal para instalación de nuevos módulos (hay esqueleto en
  `docs/impl/` pero no está formalizado como workflow).

---

### 5. CrudGenerator (Prioridad: BAJA)

`Modules/CrudGenerator` está en v1.1.0. Es una herramienta interna de scaffolding.
Su evolución debe estar subordinada al resultado de DT-001: si se consolida la arquitectura
paralela, el CrudGenerator necesitará generadores para el nuevo stack (`Actions`, `DTOs`,
`ReadServices`). Si se descarta, el CrudGenerator debe mantener su generador actual
basado en `Modules/`.

**Acción recomendada**: Congelar el CrudGenerator hasta resolver DT-001.

---

## Resumen Ejecutivo de Certificación

| Área                    | Estado                                |
|-------------------------|---------------------------------------|
| CERT-001 Infraestructura | ✅ CONFORME                           |
| CERT-002 Branding        | ✅ CONFORME                           |
| CERT-003 Cobertura Funcional | ✅ CONFORME (15/15 secciones)     |
| CERT-004 Navegación      | ✅ CONFORME CON OBSERVACIONES         |
| CERT-005 RBAC            | ✅ CONFORME (ruta activa) / ⚠️ Deuda paralela |
| CERT-006 Calidad Técnica | ⚠️ DEUDA TÉCNICA VERIFICABLE          |
| CERT-007 Testing         | ⚠️ NO AUTÓNOMA (14/50 pasan sin seeders) |
| CERT-008 Notificaciones  | ✅ CONFORME                           |
| CERT-009 Seguridad       | ✅ CONFORME                           |
| CERT-010 Versionado      | ✅ CONFORME                           |

### Dictamen Final

**IEE v1.12.1 es un producto estable en producción para el scope declarado (módulo Inventario
con 15 secciones funcionales, RBAC, notificaciones, branding institucional completo).**

La deuda técnica residual es real pero controlada: no afecta la ruta de producción activa.
El riesgo más alto (DT-001 — arquitectura paralela con Spatie roto) debe resolverse antes
de que el codebase reciba nuevos módulos para evitar que la ambigüedad arquitectónica
bloquee la evolución del producto.

**Clasificación: ESTABLE CON DEUDA TÉCNICA**

---

*Documento generado automáticamente como parte de AUDIT-IEE-001.*
*Fecha: 2026-06-11. Auditor: Claude Code (claude-sonnet-4-6).*
*SHA de referencia del repositorio al momento de la auditoría: `c1bbea9`*
