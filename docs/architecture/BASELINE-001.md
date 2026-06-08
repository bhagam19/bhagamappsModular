# BASELINE-001 — Estado del Proyecto BhagamApps Modular

**Fecha:** 2026-06-08
**Versión de plataforma:** BhagamApps v1.4.0
**Commit HEAD:** `6ec33b0`
**Elaborado después de:** IMPL-GIT-001 (recuperación de control de versiones)

---

## 1. Estado Git

| Campo | Valor |
|---|---|
| Rama activa | `main` |
| HEAD | `6ec33b0` — `docs(workflow): establish mandatory git synchronization workflow` |
| Cambios sin commit | Ninguno — working tree clean |
| Sincronización con GitHub | ✅ Sincronizado — `origin/main = 6ec33b0` |
| Remote | `https://github.com/bhagam19/bhagamappsModular.git` |
| Identidad git local | `Adolfo Ruiz <bhagam19@gmail.com>` |

### Ramas

| Rama | SHA | Descripción |
|---|---|---|
| `main` | `6ec33b0` | Estado de producción — rama activa |
| `legacy-github` | `6428eb5` | Historia de 82 commits previos (hasta 2025-06-28) |
| `origin/main` | `6ec33b0` | Sincronizado ✅ |
| `origin/legacy-github` | `6428eb5` | Sincronizado ✅ |

### Historial de commits propios

```
6ec33b0  docs(workflow): establish mandatory git synchronization workflow
54f20e3  docs(git): document repository recovery and migration
470958f  chore(git): finalize repository migration and tracking configuration
3f6d944  feat: estado de producción 2026-06-08 — BhagamApps v1.4.0
```

---

## 2. Arquitectura y Stack

### Framework y paquetes de producción

| Paquete | Versión | Rol |
|---|---|---|
| PHP | 8.4.14 | Runtime |
| Laravel Framework | 11.44.7 | Core |
| Livewire | 3.6.3 | UI reactiva (principal) |
| nwidart/laravel-modules | 12.0.3 | Arquitectura modular |
| jeroennoten/laravel-adminlte | 3.15.0 | UI / AdminLTE 3 |
| almasaeed2010/adminlte | 3.2.0 | Assets AdminLTE |
| Laravel Fortify | 1.25.4 | Autenticación |
| Laravel Jetstream | 5.3.6 | Perfil + 2FA |
| Laravel Sanctum | 4.1.1 | API auth tokens |
| barryvdh/laravel-dompdf | 3.1.x | PDF (DomPDF) |
| barryvdh/laravel-snappy | 1.0.4 | PDF (wkhtmltopdf) |
| league/csv | 9.23.x | Exportación CSV |

### Configuración de producción

| Variable | Valor | Estado |
|---|---|---|
| `APP_ENV` | `production` | ✅ |
| `APP_DEBUG` | `false` | ✅ |
| `LOG_LEVEL` | `error` | ✅ |
| `SESSION_SECURE_COOKIE` | *comentado* | ⚠️ |
| `MAIL_MAILER` | `log` | ❌ |
| `QUEUE_CONNECTION` | `sync` | ⚠️ |
| `CACHE_STORE` | `file` | ⚠️ |

### Estructura de directorios raíz

```
bhagamappsModular/
├── Modules/            ← 4 módulos nwidart
├── app/                ← Core Laravel (Controllers, Providers, etc.)
├── bootstrap/          ← app.php con pipeline L11 + cache
├── config/             ← 21 archivos de config, incluye versiones.php
├── database/           ← 29 migraciones, seeders
├── docs/               ← Documentación técnica completa
├── lang/               ← Internacionalización
├── public/             ← Punto de entrada HTTP
├── resources/          ← Views core (auth, dashboard, components)
├── routes/             ← web.php, api.php
├── stubs/              ← 67 stubs nwidart personalizados
├── storage/            ← Logs, cache, sessions
└── tests/              ← PHPUnit (sin tests escritos actualmente)
```

---

## 3. Módulos

### Versiones actuales

```php
'BhagamApps'    => '1.4.0'   // plataforma
'User'          => '2.1.1'
'Inventario'    => '2.4.0'
'Apps'          => '1.0.0'
'CrudGenerator' => '1.1.0'
```

Todos los módulos están habilitados (`modules_statuses.json: true`).

---

### 3.1 Módulo User — v2.1.1 ★ COMPLETO / PRODUCCIÓN

**Archivos:** 56 PHP | 21 vistas Blade | 14 componentes Livewire

#### Entidades

| Entidad | Tabla | Descripción |
|---|---|---|
| `User` | `users` | Usuario con `nombres`, `apellidos`, `userID`, `email`, `role_id` |
| `Role` | `roles` | Rol con `nombre`, `descripcion`, `app_id` |
| `Permission` | `permissions` | Permiso con `nombre`, `slug`, `descripcion`, `categoria` |

#### Componentes Livewire

**Usuarios:**
- `UserIndex` — listado paginado, búsqueda
- `EditarNombresUser`, `EditarApellidosUser`, `EditarEmailUser`, `EditarRolUser`, `EditarUserIDUser` — edición inline con verificación de permiso `editar-usuarios`

**Roles:**
- `RolesIndex` — listado de roles
- `EditarNombreRole`, `EditarDescripcionRole`, `EditarRolePermissions` — edición inline

**Permisos:**
- `PermissionsIndex` — listado de permisos
- `EditarNombrePermission`, `EditarDescripcionPermission`, `EditarCategoriaPermission`

#### Rutas (protegidas con middleware `permission:*`)

```
/user/usuarios       → middleware: permission:ver-usuarios
/user/roles          → middleware: permission:ver-roles
/user/permissions    → middleware: permission:ver-permisos
```

#### RBAC en producción

| Tabla | Registros |
|---|---|
| `users` | 116 |
| `roles` | 7 (Rector, Coordinador, Auxiliar, Docente, Estudiante + otros) |
| `permissions` | 25 |
| `permission_role` | 80 (post IMPL-003, sin duplicados, con UNIQUE constraint) |
| `permission_user` | 0 (permisos directos por usuario — no usados actualmente) |

#### Estado: ✅ Funcional en producción

---

### 3.2 Módulo Inventario — v2.4.0 ★ FUNCIONAL / SIN DATOS

**Archivos:** 93 PHP | 23 vistas Blade | 14 componentes Livewire

#### Entidades (15)

| Entidad | Tabla | Descripción |
|---|---|---|
| `Bien` | `bienes` | Bien institucional — entidad central. SoftDeletes. |
| `Detalle` | `detalles` | Especificaciones técnicas del bien |
| `BienImagen` | `bienes_imagenes` | Imágenes del bien |
| `Categoria` | `categorias` | Clasificación del bien |
| `Estado` | `estados` | Estado físico (bueno, regular, malo, etc.) |
| `Almacenamiento` | `almacenamientos` | Ubicación física de almacén |
| `Dependencia` | `dependencias` | Departamento/dependencia responsable |
| `Ubicacion` | `ubicaciones` | Ubicación geográfica de dependencia |
| `Mantenimiento` | `mantenimientos` | Tipo de mantenimiento |
| `MantenimientoProgramado` | `mantenimientos_programados` | Mantenimiento programado por bien |
| `HistorialModificacionBien` | `historial_modificaciones_bienes` | Auditoría de cambios en bienes |
| `HistorialDependenciaBien` | `historial_dependencias_bienes` | Auditoría de transferencias entre dependencias |
| `HistorialEliminacionBien` | `historial_eliminaciones_bienes` | Auditoría de eliminaciones |
| `BienResponsable` | *(sin tabla propia visible)* | Modelo sin migración identificada |

#### Esquema de `bienes` (tabla central)

| Campo | Tipo | Nullable | Nota |
|---|---|---|---|
| `id` | bigint | NO | PK |
| `nombre` | varchar(100) | YES | |
| `cantidad` | int | YES | |
| `serie` | varchar(40) | YES | |
| `origen` | varchar(40) | YES | |
| `fecha_adquisicion` | date | YES | |
| `precio` | **float** | YES | ⚠️ DEUDA — debe ser DECIMAL |
| `categoria_id` | bigint | YES | FK |
| `dependencia_id` | bigint | YES | FK |
| `almacenamiento_id` | bigint | YES | FK |
| `estado_id` | bigint | YES | FK |
| `mantenimiento_id` | bigint | YES | FK |
| `observaciones` | varchar(200) | YES | |
| `deleted_at` | timestamp | YES | SoftDeletes |

#### Componentes Livewire

**Bienes:**
- `BienesIndex` — listado principal con filtros, vista por rol
- `EditarCampoBien` — edición inline de campos
- `EditarDetalleBien`, `EditarDetalleBienModal` — gestión de detalles

**Historial Eliminaciones (HEB):**
- `HebIndex` — listado de solicitudes de eliminación con flujo de aprobación
- `NotificacionHeb` — notificaciones de solicitudes pendientes

**Historial Modificaciones (HMB):**
- `HmbIndex` — listado de modificaciones con flujo de aprobación
- `NotificacionHmb` — notificaciones de modificaciones pendientes

**Actas:**
- `ActaEntregaIndex` — gestión de actas de entrega
- `ActaPDF`, `ActaPrinter` — generación e impresión de PDFs

**Notificaciones:**
- `Notificaciones` — centro de notificaciones
- `NotificacionesDropdown` — dropdown en navbar
- `NotificacionesIcono` — ícono con contador

#### Rutas

```
GET  inventario/bienes          → BienesIndex (Livewire)
GET  inventario/heb             → HebIndex (Livewire)
GET  inventario/hmb             → HmbIndex (Livewire)
GET  inventario/actas           → ActaEntregaIndex
GET  inventario/actas/{id}/pdf  → PDF generation
GET  api/v1/inventarios         → REST API (autenticación: ¿?)
```

#### Datos en producción

| Tabla | Registros |
|---|---|
| `bienes` | **0** — sistema configurado, sin datos |
| `categorias` | **0** — sin seed |
| `estados` | **0** — sin seed |
| `almacenamientos` | **0** — sin seed |
| `dependencias` | **0** — sin seed |
| `ubicaciones` | **0** — sin seed |

**El módulo es funcional pero no operativo — requiere datos de referencia antes de poder registrar bienes.**

#### Estado: ⚠️ Funcional / Sin datos / precio con FLOAT

---

### 3.3 Módulo Apps — v1.0.0 ★ ESTABLE / BÁSICO

**Archivos:** 14 PHP | 2 vistas Blade | 0 Livewire

#### Función

Catálogo de aplicaciones institucionales. Cada app tiene `nombre`, `ruta`, `habilitada`, y puede asignarse a usuarios a través del pivote `app_user` (con campo `activo`).

#### Estado en producción

| Tabla | Registros |
|---|---|
| `apps` | 12 apps registradas |
| `app_user` | 0 asignaciones activas |

#### Rutas

```
GET  /apps         → listado
POST /apps         → crear
GET  /apps/{id}    → ver
PUT  /apps/{id}    → actualizar
DEL  /apps/{id}    → eliminar
```

#### Observaciones

- `HomeController` (`Ppal\`) consulta las apps del usuario mediante la relación con pivote `activo` y `habilitada` (corregido en IMPL-001)
- No tiene gestión de asignación de apps a usuarios visible en rutas — la asignación parece manual o por seeder
- `roles` tiene FK a `apps` (`app_id`), lo que sugiere que los roles son específicos por app

#### Estado: ✅ Estable / Funcionalidad mínima

---

### 3.4 Módulo CrudGenerator — v1.1.0 ★ EXPERIMENTAL

**Archivos:** 20 PHP | 2 vistas | 7 servicios | 2 comandos Artisan

#### Función

Genera módulos CRUD completos automáticamente mediante comandos Artisan:

```bash
php artisan make:crud NombreModelo   # genera rutas, vistas, Livewire, menú, permisos
php artisan crud:clean NombreModelo  # elimina lo generado
```

#### Servicios de generación (7)

| Servicio | Responsabilidad |
|---|---|
| `CrudGeneratorService` | Orquestador principal |
| `LivewireGenerator` | Genera componente Livewire con CRUD inline |
| `ViewGenerator` | Genera vistas Blade (index, create, edit, show) |
| `RouteGenerator` | Añade rutas al módulo |
| `MenuGenerator` | Añade ítem al menú AdminLTE |
| `PermissionGenerator` | Crea permisos en BD |
| `GateGenerator` | Define gates en AuthServiceProvider |
| `RuleGenerator` | Genera reglas de validación |

#### Stubs personalizados

67 stubs en `stubs/nwidart-stubs/` que definen las plantillas de generación. Son editables para personalizar el output del generador.

#### Estado en producción

- No hay módulos generados visibles en `Modules/`
- `MakeCrudCommand` mejorado en el último commit de GitHub (`6428eb5`) para ordenar por `nombre_completo` en relaciones con usuarios
- Sin documentación de uso (qué campos acepta, cómo configurar modelos, limitaciones)
- Rutas API `api/v1/crudgenerators` registradas pero sin uso aparente en producción

#### Estado: 🔬 Experimental / No documentado / No en uso activo

---

## 4. Base de Datos

### Resumen de migraciones

**29 migraciones — todas ejecutadas, ninguna pendiente.**

| Batch | Migraciones | Descripción |
|---|---|---|
| 1 | 27 | Setup inicial (2014–2025) |
| 2 | 1 | `2026_06_08_000001` — limpieza duplicados permission_role |
| 3 | 1 | `2026_06_08_000002` — UNIQUE constraint permission_role |

### Tablas y registros

| Tabla | Registros | Estado |
|---|---|---|
| `users` | 116 | Datos reales |
| `apps` | 12 | Datos reales |
| `roles` | 7 | Datos reales |
| `permissions` | 25 | Datos reales |
| `permission_role` | 80 | Datos reales (post IMPL-003) |
| `sessions` | 19 | Sesiones activas |
| `bienes` | 0 | **Sin datos** |
| `categorias` | 0 | **Sin seed** |
| `estados` | 0 | **Sin seed** |
| `almacenamientos` | 0 | **Sin seed** |
| `dependencias` | 0 | **Sin seed** |
| `ubicaciones` | 0 | **Sin seed** |
| `grupos` | 0 | **Sin uso visible** |
| `permission_user` | 0 | Sin permisos directos |

### Relaciones principales

```
users ──────── role_id ──────────────────── roles
                                              │
roles ─────────── app_id ─────────────────── apps
                                              │
users ──── app_user (pivot: activo) ──────── apps

roles ──── permission_role (pivot) ──── permissions
users ──── permission_user (pivot) ──── permissions

bienes ───────── categoria_id ──── categorias
bienes ───────── dependencia_id ── dependencias
bienes ───────── almacenamiento_id  almacenamientos
bienes ───────── estado_id ──────── estados
bienes ───────── mantenimiento_id ── mantenimientos
bienes ──── detalles (1→N)
bienes ──── bienes_imagenes (1→N)
bienes ──── historial_modificaciones_bienes (1→N)
bienes ──── historial_dependencias_bienes (1→N)
bienes ──── historial_eliminaciones_bienes (1→N)
bienes ──── mantenimientos_programados (1→N)

dependencias ──── ubicacion_id ──── ubicaciones
dependencias ──── user_id ──────── users  (responsable)
```

---

## 5. Documentación existente

### Archivos `docs/` (18 documentos markdown)

| Archivo | Líneas | Contenido |
|---|---|---|
| `docs/architecture/DEVELOPMENT_WORKFLOW.md` | 239 | Flujo obligatorio de desarrollo (ADR-WORKFLOW-001) |
| `docs/architecture/MODULE_VERSIONING_UI.md` | 168 | Sistema de versionado en UI |
| `docs/architecture/BASELINE-001.md` | *este archivo* | — |
| `docs/adr/ADR-001-Modular-Architecture.md` | 65 | Decisión: arquitectura modular con nwidart |
| `docs/adr/ADR-002-Livewire-First-Strategy.md` | 81 | Decisión: Livewire como capa UI principal |
| `docs/adr/ADR-003-CrudGenerator-Centric-Development.md` | 73 | Decisión: CrudGenerator como herramienta de desarrollo |
| `docs/adr/ADR-004-Modular-Versioning.md` | 153 | Decisión: versionado semántico independiente por módulo |
| `docs/audits/AUDIT-001-Changelog-Versioning.md` | 176 | Modal de changelog + correcciones SemVer |
| `docs/audits/AUDIT-002A-Registro-Roles.md` | 309 | Auditoría de usuarios con roles privilegiados |
| `docs/audits/AUDIT-003-PermissionRole-Duplicates.md` | 137 | 76 duplicados en permission_role |
| `docs/impl/IMPL-001-Critical-Fixes.md` | 172 | Correcciones críticas de producción |
| `docs/impl/IMPL-002-Security-Hardening.md` | 230 | Hardening de seguridad |
| `docs/impl/IMPL-003-PermissionRole-Cleanup.md` | 182 | Limpieza de permission_role |
| `docs/impl/IMPL-GIT-001.md` | 167 | Recuperación e inicialización Git |
| `docs/impl/backups/permission_role_before_cleanup.sql` | — | Backup SQL pre-IMPL-003 (21 KB) |
| `docs/changelog/bhagamapps.md` | 120 | Historial de plataforma hasta v1.4.0 |
| `docs/changelog/inventario.md` | 109 | Historial Inventario hasta v2.4.0 |
| `docs/changelog/user.md` | 81 | Historial User hasta v2.1.1 |
| `docs/changelog/crudgenerator.md` | 40 | Historial CrudGenerator hasta v1.1.0 |
| `docs/changelog/apps.md` | 20 | Historial Apps v1.0.0 |

### Carpetas sin contenido

| Carpeta | Estado |
|---|---|
| `docs/releases/` | Vacío (`.gitkeep`) |
| `docs/roadmap/` | Vacío (`.gitkeep`) |
| `docs/ddom/` | Vacío (`.gitkeep`) |

### README.md (118 líneas)

El README actual documenta la filosofía del proyecto (software libre, comunitario) pero **carece de**:
- Instrucciones de instalación técnica
- Requisitos de sistema
- Pasos de configuración del entorno
- Estructura de módulos y cómo usarlos
- Documentación de la API

---

## 6. Estado funcional por módulo

| Módulo | Versión | UI | RBAC | API | Datos | Estado general |
|---|---|---|---|---|---|---|
| User | v2.1.1 | ✅ Livewire completo | ✅ Middleware activo | — | ✅ 116 usuarios | **PRODUCCIÓN** |
| Inventario | v2.4.0 | ✅ Livewire completo | ✅ Middleware activo | ⚠️ Sin auth visible | ❌ 0 bienes | **FUNCIONAL / SIN DATOS** |
| Apps | v1.0.0 | ⚠️ Básico | ⚠️ Parcial | — | ✅ 12 apps | **ESTABLE / BÁSICO** |
| CrudGenerator | v1.1.0 | ⚠️ Solo index | ⚠️ Parcial | ⚠️ Sin auth | — | **EXPERIMENTAL** |

---

## 7. Deuda técnica identificada

### DT-001 — `bienes.precio` es tipo FLOAT ★★★ CRÍTICA

```sql
bienes.precio  float  NULL
```

`FLOAT` introduce imprecisión de punto flotante inadecuada para valores patrimoniales. Un precio de `$1,500,000.00` puede almacenarse como `$1,499,999.875`. El momento de corregirlo es **ahora**, con 0 registros.

**Corrección:** Migración que cambia `float` → `DECIMAL(12,2)`.

---

### DT-002 — `SESSION_SECURE_COOKIE` comentado ★★ ALTA

```
# SESSION_SECURE_COOKIE=true
```

El sitio corre bajo HTTPS pero las cookies de sesión no tienen el flag `Secure`. Pendiente de habilitar SSL en HestiaCP.

---

### DT-003 — Email no funcional en producción ★★ ALTA

```
MAIL_MAILER=log
```

Todos los emails (recuperación de contraseña, notificaciones) van al log de Laravel en lugar de enviarse. 116 usuarios no pueden recuperar contraseñas.

---

### DT-004 — Rutas API sin autenticación verificada ★★ ALTA

```
GET  api/v1/inventarios     → ¿auth:sanctum?
GET  api/v1/crudgenerators  → ¿auth:sanctum?
```

Las rutas API REST están registradas pero no se verificó si requieren token Sanctum. Si son públicas, exponen datos de inventario.

---

### DT-005 — Tablas de catálogo sin datos de referencia ★★ MEDIA

Para que el módulo Inventario sea operativo se necesitan seeders o carga inicial de:

```
categorias      (tipos de bien: mueble, equipo, vehículo, etc.)
estados         (bueno, regular, malo, de baja, etc.)
almacenamientos (bodegas, oficinas)
ubicaciones     (sedes, campus)
dependencias    (departamentos, áreas)
```

Sin estos datos, no se puede registrar ningún bien.

---

### DT-006 — `permission_user` sin timestamps ★ BAJA

La tabla `permission_user` (permisos directos a usuarios) no tiene `created_at` / `updated_at`. No permite auditar cuándo se asignó un permiso directo.

---

### DT-007 — `grupos` tabla sin uso ★ BAJA

Tabla `grupos` creada en migración `2025_05_12_232010`, siempre en 0 registros. No tiene rutas, entidad, ni componente Livewire visible. Posiblemente planificada para una funcionalidad futura o reemplazada por `dependencias`.

---

### DT-008 — `BienResponsable` sin tabla de BD visible ★ BAJA

La entidad `Modules/Inventario/Entities/BienResponsable.php` existe pero no hay migración correspondiente visible. Podría estar obsoleta o planificada.

---

### DT-009 — `TestFiltroController` en producción ★ BAJA

```
Modules/Inventario/Http/Controllers/TestFiltroController.php
```

Controlador de prueba que no debería existir en código de producción versionado.

---

### DT-010 — CrudGenerator sin documentación de uso ★ MEDIA

El módulo tiene 7 servicios de generación y funcionalidad real, pero no hay documentación de:
- Qué parámetros acepta `make:crud`
- Qué genera exactamente (estructura del módulo resultante)
- Limitaciones conocidas
- Cómo revertir una generación

---

### DT-011 — README sin documentación técnica ★ MEDIA

El README describe la filosofía del proyecto pero no contiene instrucciones de instalación, configuración de entorno, o guía de uso de módulos. Dificulta onboarding de colaboradores.

---

### DT-012 — Sin tests escritos ★ MEDIA

El directorio `tests/` existe (PHPUnit configurado) pero no hay tests de feature ni unitarios escritos. La cobertura es 0%.

---

### DT-013 — `QUEUE_CONNECTION=sync` ★ BAJA

Las notificaciones y tareas asíncronas se procesan de forma síncrona. Para un sistema con 116 usuarios activos y workflows de aprobación de bienes, esto puede generar latencia perceptible. Mitigable con Redis + Laravel Horizon si el volumen crece.

---

## 8. Riesgos identificados

| ID | Riesgo | Probabilidad | Impacto | Prioridad |
|---|---|---|---|---|
| R-01 | Carga de bienes con `precio FLOAT` → datos monetarios corruptos | Alta (se va a cargar el inventario) | Alto | 🔴 Crítica |
| R-02 | Cookie de sesión sin flag Secure en HTTPS | Media | Medio | 🟠 Alta |
| R-03 | Email no funcional — 116 usuarios sin recuperación de contraseña | Alta (ya está en producción) | Alto | 🟠 Alta |
| R-04 | Rutas API sin autenticación → exposición de datos de inventario | Media | Alto | 🟠 Alta |
| R-05 | Inventario no operativo por falta de datos de referencia | Alta (bloquea uso del sistema) | Alto | 🟡 Media |
| R-06 | Sin tests → regresiones no detectadas en cambios futuros | Media | Medio | 🟡 Media |
| R-07 | CrudGenerator sin documentación → mal uso o código generado incorrecto | Baja | Medio | 🟡 Media |
| R-08 | `TestFiltroController` en producción → confusión + posible exposición | Baja | Bajo | 🟢 Baja |

---

## 9. Recomendaciones priorizadas

### P1 — Inmediata (antes de cargar datos de inventario)

```
IMPL-004: bienes.precio FLOAT → DECIMAL(12,2)
  Migración simple, 0 registros afectados.
  Una vez cargados los bienes, la corrección es costosa.
  Tiempo estimado: 30 minutos.
```

### P2 — Urgente (semana 1)

```
IMPL-005: Habilitar SESSION_SECURE_COOKIE
  Prerequisito: confirmar SSL activo en HestiaCP.
  Una línea en .env + php artisan config:clear.
  Tiempo estimado: 15 minutos.

IMPL-006: Configurar MAIL_MAILER con SMTP real
  116 usuarios sin recuperación de contraseña.
  Opciones: servidor propio del dominio, Gmail SMTP, Mailgun.
  Tiempo estimado: 1 hora (incluyendo pruebas).

AUDIT-004: Verificar autenticación en rutas API
  Confirmar o agregar middleware auth:sanctum.
  Tiempo estimado: 30 minutos.
```

### P3 — Importante (semana 2-3)

```
IMPL-007: Seeders de catálogos para Inventario
  Crear seeders para categorias, estados, almacenamientos,
  ubicaciones, dependencias con datos reales de la institución.
  Prerequisito para que el módulo sea operativo.
  Tiempo estimado: 2-4 horas.

IMPL-008: Documentación técnica del README
  Instrucciones de instalación, configuración, módulos.
  Tiempo estimado: 2 horas.
```

### P4 — Planificado (mes 1)

```
IMPL-009: Eliminar TestFiltroController
  Limpiar código de prueba de producción.

IMPL-010: Resolver tabla grupos
  Definir si tiene uso futuro o eliminar con migración.

IMPL-011: Timestamps en permission_user
  Migración ALTER TABLE para añadir created_at/updated_at.

IMPL-012: Documentar CrudGenerator
  Guía de uso, parámetros, ejemplos, limitaciones.
```

### P5 — Largo plazo

```
Tests: escribir feature tests para los flujos críticos
  - Registro de usuarios (RBAC)
  - Registro de bienes (validaciones)
  - Flujos de aprobación HEB/HMB

Queue: evaluar Redis/Horizon para notificaciones asíncronas
  cuando el volumen de usuarios activos crezca.
```

---

## Resumen ejecutivo

BhagamApps Modular es una plataforma de gestión institucional en producción activa con 116 usuarios reales. El sistema de RBAC y gestión de usuarios es funcional y robusto. El módulo de Inventario está técnicamente completo pero sin datos operativos.

**La acción más urgente es corregir `bienes.precio FLOAT → DECIMAL` antes de iniciar la carga de datos del inventario.** A partir de ese momento, el sistema puede comenzar su operación plena.

| Dimensión | Estado |
|---|---|
| Control de versiones | ✅ Git + GitHub sincronizados |
| Seguridad de acceso | ✅ RBAC funcional (IMPL-001/002/003) |
| Integridad de BD | ⚠️ `precio FLOAT` pendiente |
| Seguridad de sesión | ⚠️ `SESSION_SECURE_COOKIE` pendiente |
| Email | ❌ No funcional |
| API auth | ⚠️ Sin verificar |
| Datos de inventario | ⏳ Sistema listo, carga pendiente |
| Documentación técnica | ✅ Completa (excepto README e instalación) |
| Tests | ❌ 0% cobertura |
