# HOTFIX-USERS-007 — Integridad del Listado de Usuarios

**Fecha:** 2026-06-11  
**Módulo:** User (`Modules/User`)  
**Severidad:** CRÍTICA  
**Estado:** RESUELTO  
**SHA commit:** (ver git log)

---

## Síntesis del problema

Al ordenar por ID en `/iee/users/users`, el listado mostraba filas faltantes
(e.g., aparecía ID 1, 2, no 3, 4, 5, no 6...). Simultáneamente, el log de
Laravel registraba un error al intentar interactuar con usuarios recién mostrados:

```
production.ERROR: Trying to access array offset on null
  at HandleComponents.php:88
```

Ambos síntomas tienen la misma causa raíz.

---

## FASE 1 — Diagnóstico

### USRINT-001 — COUNT real

```sql
SELECT COUNT(*) FROM users;
-- Resultado: 116
```

### USRINT-002 — Comparación count vs. paginador

```
$users->total()          = 116  ✓
COUNT(*) en BD           = 116  ✓
Items en página 1 (ID)   = 25   ✓
```

No hay discrepancia entre la base de datos y el paginador.

### USRINT-003 — SQL ejecutado por UserIndex

```sql
-- Conteo:
SELECT count(*) as aggregate FROM users LEFT JOIN roles ON users.role_id = roles.id

-- Datos:
SELECT users.* FROM users LEFT JOIN roles ON users.role_id = roles.id ORDER BY users.id ASC LIMIT 25 OFFSET 0

-- Eager loading:
SELECT * FROM roles WHERE roles.id IN (5)
```

Los tres queries son correctos. No hay duplicados, no hay filas perdidas.

### USRINT-004 — Duplicados en el resultado con leftJoin

```
Filas devueltas: 116
IDs únicos:      116
Duplicados:      No
```

### USRINT-005 — Verificación en todas las páginas

| Página | Items | IDs (primeros y últimos) |
|--------|-------|--------------------------|
| 1 | 25 | 1–25 |
| 2 | 25 | 26–50 |
| 3 | 25 | 51–70, 73–77 (71 y 72 eliminados) |
| 4 | 25 | 78–102 |
| 5 | 16 | 103–118 |
| **Total** | **116** | **todos cubiertos, sin duplicados** |

### Diagnóstico concluyente

La causa NO es:
- ❌ Consulta SQL incorrecta
- ❌ Paginación desordenada
- ❌ Campos nulos o huérfanos en la BD
- ❌ Duplicados en el join

La causa ES: **ausencia de `wire:key` en los elementos raíz del `@forelse`** que itera usuarios.

---

## FASE 2 — Análisis de causa raíz

### El error en producción

```
HandleComponents.php:88
$data = $snapshot['data'];  ← TypeError: $snapshot es null
```

**Cadena causal:**

```
HandleRequests::handleUpdate()
  → $snapshot = json_decode($componentPayload['snapshot'], associative: true)
  → json_decode(null) = null  ← el browser envió snapshot null
  → LivewireManager::update(null, ...)
  → HandleComponents::update(null, ...)
  → $data = null['data']  ← ErrorException
```

### Por qué el browser envía snapshot null

Livewire 3 usa Alpine's Morph para actualizar el DOM cuando el componente padre (UserIndex) re-renderiza. El algoritmo morph compara el HTML nuevo con el DOM existente para decidir qué elementos crear, mover o eliminar.

**Sin `wire:key` en `<tr>`**: morph procesa las filas de la tabla POSICIONALMENTE. La fila en posición 1 se morfea sobre lo que había en posición 1.

Cuando el sort cambia (nombres→id) y los usuarios en la página son diferentes, morph:
1. Intenta "reutilizar" el DOM de la fila N del sort anterior para el sort nuevo
2. Crea nuevos componentes Livewire hijo para usuarios que aparecen por primera vez
3. Al crear nuevos componentes via morph (no via render inicial), algunos no reciben correctamente su snapshot JS
4. Esos componentes quedan con `snapshot = null` en el estado JS de Livewire
5. Al interactuar con ellos → el browser envía `{snapshot: null}` al server → ErrorException

El resultado visual: algunas filas aparecen incompletas o "desaparecen" porque el DOM de esa fila tiene componentes Livewire en estado inconsistente.

### Evidencia de la documentación de Livewire 3

> "When iterating over items in a Livewire template, Livewire recommends adding a `wire:key` directive to the root element of each loop iteration so Livewire can identify each item when re-rendering."

---

## FASE 2 — Corrección

### Cambio único: `user-index.blade.php`

**Desktop table (`@forelse` en `<tbody>`):**

```diff
- <tr>
+ <tr wire:key="row-{{ $user->id }}">
```

**Mobile accordion (`@forelse` en `<div id="accordionMobile">`):**

```diff
- <div class="card mb-2">
+ <div class="card mb-2" wire:key="card-{{ $user->id }}">
```

Con `wire:key`, el algoritmo morph de Livewire/Alpine identifica cada fila por el ID del usuario (no por posición). Cuando el sort cambia:
- Las filas cuyos usuarios siguen en la página se MUEVEN al orden correcto (los componentes hijo mantienen su snapshot)
- Las filas de usuarios que llegan de otras páginas se CREAN con snapshot correcto
- Las filas de usuarios que salen de la página se ELIMINAN limpiamente

---

## Validaciones

- ✓ V-001: Todos los usuarios aparecen (116/116, sin gaps, paginación correcta)
- ✓ V-002: No existen duplicados en ninguna página
- ✓ V-003: Total visible coincide con COUNT(*) real (116)
- ✓ V-004: Orden por ID ASC/DESC muestra los 25 usuarios correctos en cada página
- ✓ V-005: Orden por rol ASC/DESC produce resultados completos y consistentes
- ✓ V-006: Búsqueda correcta (resultados sin gaps)
- ✓ V-007: Sin regresiones — búsqueda reactiva, filtros, ordenamiento, CRUD, gestión de contraseñas y estado funcionan sin cambios

---

## Archivos modificados

| Archivo | Tipo de cambio |
|---------|---------------|
| `Modules/User/Resources/views/livewire/user/user-index.blade.php` | `wire:key` en `<tr>` y `.card` de los loops |
| `docs/impl/HOTFIX-USERS-007-Integridad-Listado-Usuarios.md` | Este documento |
| `docs/changelog/user.md` | v2.4.4 |
| `docs/changelog/iee.md` | v1.16.4 |
| `VERSIONING.md` | User v2.4.4, IEE v1.16.4 |
| `config/versiones.php` | User v2.4.4, IEE v1.16.4 |
