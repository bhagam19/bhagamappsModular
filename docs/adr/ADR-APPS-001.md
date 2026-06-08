# ADR-APPS-001 — Arquitectura del Módulo Central de Aplicaciones

**Estado:** Aceptado
**Fecha:** 2026-06-08
**Contexto:** BhagamAppsModular — IMPL-APPS-001
**Relacionado con:** ADR-001 (Arquitectura Modular), AUDIT-APPS-001

---

## Contexto

El módulo Apps administra el catálogo de aplicaciones disponibles en la plataforma. Actualmente la autorización es por usuario individual (`app_user`). El objetivo es evolucionar hacia autorización por rol, preservando la asignación individual como mecanismo de fallback/override.

---

## Decisiones

### D1 — Tabla `app_role` como mecanismo primario de autorización

Se crea una tabla pivot `app_role` (app_id, role_id) para asociar aplicaciones a roles. Esto permite que todos los usuarios de un rol vean automáticamente las aplicaciones asignadas a ese rol.

**Alternativas rechazadas:**
- Reemplazar `app_user` por `app_role`: descartado por ruptura de datos existentes.
- Usar `role_id` en `app_user`: el campo ya existe pero mezcla dos semánticas distintas.

**Consecuencia:** `App::visiblesPara($user)` combina ambas fuentes: apps del rol del usuario + apps asignadas individualmente al usuario.

---

### D2 — Columnas de metadatos en tabla `apps`

Se agrega migración aditiva (no destructiva) con:

| Columna | Tipo | Propósito |
|---|---|---|
| slug | string unique | Identificador URL-safe |
| descripcion | text nullable | Descripción breve |
| icono | string nullable | Clase CSS (ej. `fas fa-boxes`) |
| color | string nullable | Color hex o clase CSS |
| orden | unsignedInteger default 99 | Orden de visualización |

El campo `imagen` existente se conserva para compatibilidad con el seeder actual.

---

### D3 — Método estático `App::visiblesPara($user)`

Se implementa como método estático en el modelo App, no como scope de Eloquent, para claridad semántica y uso explícito en controladores:

```php
App::visiblesPara(auth()->user())
```

Retorna: apps habilitadas donde el rol del usuario está en `app_role` OR el usuario está en `app_user` con `activo=true`.

---

### D4 — Componente Livewire `AppsIndex` para administración

Se crea `Modules/Apps/Livewire/AppsIndex.php` para el panel de administración de apps. Funcionalidades:
- Listado de todas las apps
- Toggle de `habilitada`
- Gestión de roles asignados

El componente sigue el patrón establecido en `BienesIndex` (Inventario) y `UserIndex` (User).

---

### D5 — Comando Artisan `apps:sync`

Se implementa `Modules/Apps/Console/Commands/SyncApps.php` que itera `Module::all()` y crea registros en `apps` para módulos no registrados.

**Restricción:** El comando NO asigna roles automáticamente. Solo registra el catálogo. La asignación de roles es manual.

---

### D6 — ServiceProvider actualizado con patrón Inventario

El `AppsServiceProvider` se actualiza siguiendo el patrón del `InventarioServiceProvider`:
- Auto-registro de componentes Livewire desde `Modules/Apps/Livewire/`
- `loadMigrationsFrom` para cargar migraciones del módulo
- Registro del comando `apps:sync`

---

### D7 — HomeController delega a `App::visiblesPara()`

El `HomeController` se simplifica usando el método centralizado. Esto desacopla la lógica de autorización del controlador.

```php
// Antes
$apps = auth()->user()->apps()->wherePivot('activo', true)->where('habilitada', true)->get();

// Después
$apps = App::visiblesPara(auth()->user());
```

---

## Consecuencias

| Positiva | Descripción |
|---|---|
| Autorización por rol | Un admin puede dar acceso a una app a todos los usuarios de un rol en un paso |
| Centralización | `App::visiblesPara()` es el único punto de verdad para apps visibles |
| No destructivo | `app_user` se preserva; los usuarios con asignaciones individuales siguen viendo sus apps |
| Extensible | Futuras fuentes de autorización (teams, dependencias) pueden agregarse en `visiblesPara()` |

| Negativa | Mitigación |
|---|---|
| Dos mecanismos de autorización | Documentación clara en ADR y código |
| Migración requiere `php artisan migrate` | La tabla `apps` debe existir antes de `app_role` — el orden de migraciones garantiza esto |

---

## Estándares aplicados

- Controladores delgados (HomeController delega a modelo)
- Modelos con lógica de dominio (`visiblesPara`)
- Convención nWidart: Livewire auto-registrado desde `Modules/Apps/Livewire/`
- Sin Spatie Permissions (el proyecto usa sistema propio Role/Permission)
