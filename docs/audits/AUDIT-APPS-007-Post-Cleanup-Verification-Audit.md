# AUDIT-APPS-007 — Post-Cleanup Verification Audit

**Estado:** COMPLETADO  
**Responsable:** Auditoría  
**Fecha:** 2026-06-09  
**Predecesor:** IMPL-APPS-006 — Legacy Application Registry Cleanup  
**Commit predecesor:** `e22e505`

---

## 1. Objetivo

Verificar que IMPL-APPS-006 eliminó completamente los hallazgos críticos identificados en AUDIT-APPS-006, y determinar si el módulo Apps puede declararse `BASELINE-APPS-1.0`.

---

## 2. Esquema verificado

### Tabla `apps`

| Campo        | Tipo                    | Null | Default |
|--------------|-------------------------|------|---------|
| id           | bigint(20) unsigned     | NO   |         |
| nombre       | varchar(255)            | NO   |         |
| slug         | varchar(255)            | YES  |         |
| ruta         | varchar(255)            | NO   |         |
| descripcion  | text                    | YES  |         |
| imagen       | varchar(255)            | YES  |         |
| icono        | varchar(255)            | YES  |         |
| color        | varchar(20)             | YES  |         |
| user_id      | bigint(20) unsigned     | YES  |         |
| habilitada   | tinyint(1)              | NO   | 1       |
| orden        | int(10) unsigned        | NO   | 99      |
| created_at   | timestamp               | YES  |         |
| updated_at   | timestamp               | YES  |         |

---

## 3. Hallazgos verificados (AUDIT-APPS-006)

### H-001 — Registros legacy sin slug

**Resultado esperado:** 0 registros

```sql
SELECT COUNT(*) FROM apps WHERE slug IS NULL OR slug = '';
```

**Evidencia:**

```
H-001 sin slug: 0
```

**Estado:** ✅ CLEARED — 0 registros sin slug

---

### H-002 — Duplicados de nombres y rutas

**Resultado esperado:** 0 duplicados

```sql
-- Slugs duplicados
SELECT slug, COUNT(*) as cnt FROM apps WHERE slug IS NOT NULL AND slug != ''
GROUP BY slug HAVING cnt > 1;

-- Nombres duplicados
SELECT nombre, COUNT(*) as cnt FROM apps GROUP BY nombre HAVING cnt > 1;

-- Rutas duplicadas
SELECT ruta, COUNT(*) as cnt FROM apps WHERE ruta IS NOT NULL AND ruta != ''
GROUP BY ruta HAVING cnt > 1;
```

**Evidencia:**

```
H-002 dup slug:   0
H-002 dup nombre: 0
H-002 dup ruta:   0
```

**Estado:** ✅ CLEARED — Sin duplicados en slug, nombre ni ruta

---

### H-003 — Integridad del catálogo

**Resultado esperado:** Todos los registros activos poseen nombre, slug y ruta

**Evidencia:**

```
H-003 total: 12 | habilitadas: 2 | sin nombre: 0 | sin slug: 0 | sin ruta: 0
```

**Estado:** ✅ CLEARED — Las 2 apps habilitadas poseen nombre, slug y ruta completos

---

### H-004 — Integridad referencial

**Resultado esperado:** Sin huérfanos en app_role, app_user, roles.app_id

```sql
-- Huérfanos en app_role
SELECT COUNT(*) FROM app_role WHERE app_id NOT IN (SELECT id FROM apps);

-- Huérfanos en app_user
SELECT COUNT(*) FROM app_user WHERE app_id NOT IN (SELECT id FROM apps);

-- Huérfanos en roles.app_id
SELECT COUNT(*) FROM roles WHERE app_id IS NOT NULL AND app_id NOT IN (SELECT id FROM apps);
```

**Evidencia:**

```
H-004 orphan app_role:   0
H-004 orphan app_user:   0
H-004 orphan roles.app_id: 0
```

**Estado:** ✅ CLEARED — Sin huérfanos en ninguna tabla relacionada

---

## 4. Validaciones

### V-001 — Inventario del catálogo actual

**Total:** 12 registros | **Habilitadas:** 2 | **Inactivas:** 10

| ID | Estado   | Slug                 | Nombre                 | Ruta                    | Orden |
|----|----------|----------------------|------------------------|-------------------------|-------|
| 16 | ACTIVA   | user                 | Usuarios               | /user                   | 1     |
| 15 | ACTIVA   | inventario           | Inventario             | /inventario/bienes      | 2     |
| 13 | INACTIVA | apps                 | Aplicaciones           | /apps/admin             | 3     |
| 17 | INACTIVA | biblioteca           | Biblioteca             | /biblioteca             | 4     |
| 18 | INACTIVA | sinai-vs-simat       | SINAI vs SIMAT         | /SvS                    | 10    |
| 19 | INACTIVA | planeador            | Planeador              | /planeador              | 11    |
| 20 | INACTIVA | edu-inclusiva        | EduInclusiva           | /eduInclusiva           | 12    |
| 21 | INACTIVA | cte                  | CTE                    | /cte                    | 13    |
| 22 | INACTIVA | creador-examenes     | Creador de Exámenes    | /creadorExamenes        | 14    |
| 23 | INACTIVA | prestamo-tabletas    | Préstamo Tabletas      | /prestamoTabletas       | 15    |
| 24 | INACTIVA | evaluar-para-avanzar | Evaluar para Avanzar   | /evaluarParaAvanzar     | 16    |
| 14 | INACTIVA | crudgenerator        | crudgenerator          | /crudgenerator          | 99    |

**Estado:** ✅ OK

---

### V-002 — Unicidad de slug, ruta y nombre

| Campo  | Resultado          |
|--------|--------------------|
| slug   | OK — 0 duplicados  |
| ruta   | OK — 0 duplicados  |
| nombre | OK — 0 duplicados  |

**Estado:** ✅ OK

---

### V-003 — visiblesPara() por rol

La función `App::visiblesPara(User $user)` filtra por `habilitada = true` y verifica pertenencia vía `app_role` (por rol) o `app_user.activo = true` (por usuario individual).

**Asignaciones en app_role:**

| App (slug)   | Habilitada | Roles asignados            |
|--------------|------------|----------------------------|
| apps         | ❌ INACTIVA | Administrador, Rector      |
| inventario   | ✅ ACTIVA   | Administrador, Rector      |
| user         | ✅ ACTIVA   | Administrador, Rector      |
| biblioteca   | ❌ INACTIVA | Administrador, Rector      |

**Resultado visiblesPara() por rol (solo apps habilitadas):**

| Rol           | ID | Apps visibles                  | Count |
|---------------|----|--------------------------------|-------|
| Administrador | 1  | inventario, user               | 2     |
| Rector        | 2  | inventario, user               | 2     |
| Coordinador   | 3  | *(sin asignaciones activas)*   | 0     |
| Auxiliar      | 4  | *(sin asignaciones activas)*   | 0     |
| Docente       | 5  | *(sin asignaciones activas)*   | 0     |
| Estudiante    | 6  | *(sin asignaciones activas)*   | 0     |
| Invitado      | 7  | *(sin asignaciones activas)*   | 0     |

**Observación O-001:** Coordinador y otros roles no tienen apps asignadas en `app_role`. Esto es comportamiento esperado del estado actual del catálogo — no es un defecto de integridad. Las apps se pueden asignar a roles cuando se habiliten nuevos módulos.

**Estado:** ✅ OK — La función opera correctamente. Los resultados son coherentes con el estado de `habilitada` y las asignaciones existentes en `app_role`.

---

### V-004 — Dashboard

**Flujo verificado:**

```
GET /                          
→ HomeController::index()      
→ App::visiblesPara(auth()->user())  
→ view('ppal.index', compact('apps'))  
→ @include('apps::index', ['apps' => $apps])  
→ @forelse ($apps as $app) / @empty fallback ✓
```

- `HomeController` (`app/Http/Controllers/Ppal/HomeController.php`): llama `visiblesPara()` y pasa `$apps` a la vista.
- Vista `apps::index` (`Modules/Apps/resources/views/index.blade.php`): renderiza tarjetas con `@forelse` y fallback a "No tienes aplicaciones disponibles." cuando la colección está vacía.
- Versión escritorio y móvil cubiertas.
- Los enlaces usan `url($app->ruta)` — compatible con subdirectorio.

**Estado:** ✅ OK

---

### V-005 — Sidebar

**Flujo verificado:**

```
left-sidebar.blade.php  
→ @auth  
→ App::visiblesPara(auth()->user())  
→ @if($appsNavLateral->isNotEmpty()) → sección "MIS MÓDULOS"  
→ @foreach con url($appNav->ruta) y active state por request()->is()
```

Archivo: `resources/views/vendor/adminlte/partials/sidebar/left-sidebar.blade.php`

- Llama `visiblesPara()` directamente en la vista bajo `@auth`.
- Renderiza sección "MIS MÓDULOS" solo si hay apps visibles.
- Usa `url()` helper — compatible con subdirectorio.
- Detecta ruta activa con `request()->is()`.

**Estado:** ✅ OK

---

### V-006 — Acceso a rutas clave

Rutas verificadas con `php artisan route:list --verbose`:

#### /apps/admin

```
GET|HEAD  apps/admin  apps.admin.index  AppController@index
          ⇂ web
          ⇂ Authenticate (auth)
          ⇂ EnsureEmailIsVerified (verified)
          ⇂ CheckPermission:ver-apps (permission)
```

**Estado:** ✅ OK

#### /users/users

```
GET|HEAD  users/users  user.users.index  UserController@index
          ⇂ web
          ⇂ Authenticate (auth)
          ⇂ CheckAppAccess:user (app.access)
          ⇂ CheckPermission:ver-usuarios (permission)
```

**Estado:** ✅ OK

#### /inventario/bienes

```
GET|HEAD  inventario/bienes  inventario.bienes.index  BienController@index
          ⇂ web
          ⇂ Authenticate (auth)
          ⇂ CheckAppAccess:inventario (app.access)
          ⇂ CheckPermission:ver-bienes (permission)
```

**Estado:** ✅ OK

---

### V-007 — Middleware

| Middleware    | /apps/admin | /users/users | /inventario/bienes | Estado   |
|---------------|-------------|--------------|--------------------|----------|
| `web`         | ✅           | ✅            | ✅                  | OK       |
| `auth`        | ✅           | ✅            | ✅                  | OK       |
| `verified`    | ✅           | —            | —                  | Ver O-002|
| `permission`  | ✅           | ✅            | ✅                  | OK       |
| `app.access`  | —           | ✅            | ✅                  | OK       |

**Nota sobre `verified`:** El módulo Apps aplica `verified` en sus rutas. Los módulos User e Inventario no lo aplican — esto es consistente con su configuración propia y no representa un defecto del módulo Apps.

**Nota sobre `app.access` en /apps/admin:** La ruta de administración del catálogo usa `permission:ver-apps` en lugar de `app.access:apps`. Esto es diseño intencional: el panel de administración de Apps está protegido por permiso directo, no por visibilidad en el catálogo. El slug `apps` permanece inactivo deliberadamente.

**Observación O-002 (informativa):** La ausencia de `verified` en módulos User e Inventario es una decisión de cada módulo, fuera del alcance de AUDIT-APPS-007.

**Estado:** ✅ OK — Middleware del módulo Apps correcto y coherente

---

## 5. Resumen de hallazgos remanentes

| ID    | Descripción                                                         | Criticidad  | Acción    |
|-------|---------------------------------------------------------------------|-------------|-----------|
| O-001 | Coordinador y otros roles sin apps asignadas en app_role            | Informativa | Ninguna   |
| O-002 | `verified` no aplicado en módulos User/Inventario (fuera de alcance)| Informativa | Ninguna   |

**No existen hallazgos críticos ni bloqueantes.**

---

## 6. Clasificación final

```
╔══════════════════════════════════════════════════╗
║                                                  ║
║        BASELINE-APPS-1.0                         ║
║        Estado: APROBADO                          ║
║                                                  ║
║  H-001  CLEARED  ✅  (0 registros sin slug)      ║
║  H-002  CLEARED  ✅  (0 duplicados)              ║
║  H-003  CLEARED  ✅  (integridad completa)       ║
║  H-004  CLEARED  ✅  (0 huérfanos)               ║
║                                                  ║
║  V-001 — V-007   ✅  TODAS SATISFACTORIAS        ║
║                                                  ║
╚══════════════════════════════════════════════════╝
```

**Clasificación:** A — **APTO PARA BASELINE-APPS-1.0**

El módulo Apps queda oficialmente cerrado para nuevas correcciones estructurales.

---

## 7. Trazabilidad

| Documento           | SHA / Referencia                         |
|---------------------|------------------------------------------|
| AUDIT-APPS-006      | `docs/audits/AUDIT-APPS-006-Application-Registry-Data-Integrity-Audit.md` |
| IMPL-APPS-006       | commit `e22e505`                         |
| AUDIT-APPS-007      | Este documento                           |

---

*Generado automáticamente — 2026-06-09*
