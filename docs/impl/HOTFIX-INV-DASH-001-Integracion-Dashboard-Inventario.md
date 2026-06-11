# HOTFIX-INV-DASH-001 — Integración del Dashboard como Página Principal de Inventario

## Estado

COMPLETADO

## Fecha

2026-06-11

## Descripción

IMPL-INV-DASH-001 había creado el Dashboard Ejecutivo correctamente en `GET /inventario`,
pero los menús laterales, el módulo Apps y la tabla `apps` en BD seguían apuntando
a `/inventario/bienes` como entrada al módulo. Este hotfix corrige todos los puntos
de navegación para que el Dashboard sea la experiencia inicial real.

---

## DASHFIX-001 — Auditoría y Hallazgos

### Archivo: `config/adminlte.php`

| Elemento | Valor anterior | Valor corregido |
|---|---|---|
| Padre "Inventario" — `route` | (ninguno — solo dropdown) | `inventario.dashboard` |
| Padre "Inventario" — `active` | (ninguno) | `['inventario']` |
| Primer ítem del submenu | "Bienes" | "Dashboard" (nuevo ítem añadido al inicio) |

### Archivo: `Modules/Apps/database/seeders/AppSeeder.php`

| Campo | Valor anterior | Valor corregido |
|---|---|---|
| `ruta` (slug=inventario) | `/inventario/bienes` | `/inventario` |

### Base de datos: tabla `apps`

| Campo | Valor anterior | Valor corregido |
|---|---|---|
| `ruta` WHERE `slug = 'inventario'` | `/inventario/bienes` | `/inventario` |

Corrección aplicada vía migración:
`Modules/Inventario/Database/Migrations/2026_06_11_000001_update_inventario_app_ruta_to_dashboard.php`

---

## DASHFIX-002 — Cambio en adminlte.php

El ítem padre "Inventario" ahora tiene `route => inventario.dashboard`.
El primer ítem del submenu es "Dashboard" apuntando a `inventario.dashboard`.
Los demás ítems del submenu permanecen sin cambios.

---

## DASHFIX-003 — Accesos desde Sidebar / Apps / Mis Módulos

- Sidebar: clic en "Inventario" navega a `/inventario` (dashboard)
- Apps: tarjeta "Inventario" apunta a `/inventario`
- Submenu: "Dashboard" es el primer ítem listado

---

## DASHFIX-004 — Bienes sigue accesible

"Bienes" permanece como segundo ítem en el submenu de Inventario.
Todos los demás ítems del menú se mantienen sin cambios.

---

## DASHFIX-005 — Confirmación de renderizado

El Dashboard renderiza KPIs, gráficas, alertas y accesos rápidos.
No existe redirección automática hacia Bienes en ningún middleware o controlador.

---

## Validaciones

- [x] V-001: Menú Inventario abre Dashboard — `route => inventario.dashboard` en padre y submenu
- [x] V-002: Apps abre Dashboard — tabla `apps.ruta` actualizada a `/inventario`
- [x] V-003: Dashboard carga correctamente — InventarioDashboard Livewire sin errores
- [x] V-004: Bienes sigue accesible — ítem "Bienes" permanece en el submenu
- [x] V-005: Sin errores 404 — ruta `inventario.dashboard` verificada en route:list
- [x] V-006: Sin errores 403 — ruta hereda middleware `app.access:inventario` del grupo
- [x] V-007: Sin regresiones de navegación — todos los demás ítems del menú permanecen inalterados

---

## Archivos Modificados

| Archivo | Cambio |
|---|---|
| `config/adminlte.php` | Añade `route` al padre "Inventario"; añade ítem "Dashboard" al inicio del submenu |
| `Modules/Apps/database/seeders/AppSeeder.php` | `ruta` Inventario: `/inventario/bienes` → `/inventario` |
| `Modules/Inventario/Database/Migrations/2026_06_11_000001_update_inventario_app_ruta_to_dashboard.php` | Migración: actualiza `apps.ruta` en BD para slug=inventario |
