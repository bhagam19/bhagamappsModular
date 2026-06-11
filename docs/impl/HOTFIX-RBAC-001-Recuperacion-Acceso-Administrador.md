# HOTFIX-RBAC-001 — Recuperación de Acceso Administrador

**Fecha:** 2026-06-11
**Autorizado por:** PMO
**Prioridad:** CRÍTICA
**Repositorio:** `/home/adolfo/web/bhagamapps.com/private/bhagamappsModular`
**URL:** http://bhagamapps.com/iee

---

## Síntoma

`rectoriaiee@entrerrios.edu.co` se autenticaba correctamente pero recibía `403 Forbidden` en:
- `/iee/users/users`
- `/iee/inventario/bienes`

```
Autenticación  = OK
Sesión         = OK
Middleware auth = OK
Autorización RBAC = FALLANDO
```

---

## FASE 1 — Diagnóstico

### RBAC-001 — Usuario

| Campo | Valor |
|---|---|
| id | 54 |
| email | rectoriaiee@entrerrios.edu.co |
| nombres | Adolfo León Ruiz Hernández |
| role_id | 2 |
| rol | Rector |
| bloqueado | No |
| permisos del rol | 77 (completos) |

### RBAC-002 — Roles

| id | Nombre | Usuarios | Permisos |
|---|---|---|---|
| 1 | Administrador | 0 | 77 |
| 2 | Rector | 1 | 77 |
| 3 | Coordinador | 5 | 8 |
| 4 | Auxiliar | 28 | 8 |
| 5 | Docente | 82 | 8 |
| 6 | Estudiante | 0 | 0 |
| 7 | Invitado | 0 | 0 |

### RBAC-003 / RBAC-005 — Causa raíz

**`app_role` = 0 registros.**

El middleware `CheckAppAccess` evalúa `App::visiblesPara($user)` que consulta la tabla `app_role`.
Sin registros, ningún rol tiene acceso a ninguna app → `abort(403)` antes del check de permisos.

**Cadena causal:**

1. `2026_06_09_200000_cleanup_legacy_apps` eliminó apps legacy (sin slug).
   El FK `ON DELETE CASCADE` eliminó los 8 registros existentes en `app_role`.
2. `AppSeeder` creó nuevas apps con slugs pero **no puebla `app_role`**.
3. `2026_06_09_000006_assign_inventario_app_to_coordinador` intentó insertar solo
   Inventario→Coordinador, pero corrió antes del `AppSeeder` → `app_id = null` → insertó nada.
4. Resultado: `app_role` vacía en entorno restaurado.

### RBAC-004 — Permisos verificados (pre-corrección)

Los permisos estaban correctamente asignados al rol Rector — el 403 era exclusivamente por `app_role`.

| Permiso | Estado |
|---|---|
| ver-usuarios | ✓ |
| ver-bienes | ✓ |
| ver-dependencias | ✓ |
| ver-categorias | ✓ |
| ver-responsables-bienes | ✓ |

---

## FASE 2 — Corrección

**Migración:** `Modules/Apps/database/migrations/2026_06_11_200000_assign_app_roles_rbac_recovery.php`

Matriz de acceso restaurada (dinámica por slug/nombre — no depende de IDs fijos):

| Rol | Apps asignadas |
|---|---|
| Administrador | user, inventario, apps |
| Rector | user, inventario, apps |
| Coordinador | user, inventario |
| Auxiliar | inventario |
| Docente | inventario |

No se modificó el `role_id` del usuario rector. El rol Rector tiene 77 permisos completos
(equivalente a Administrador). La denominación es semánticamente correcta.

```bash
php artisan migrate --force
# 2026_06_11_200000_assign_app_roles_rbac_recovery ... DONE
php artisan cache:clear
```

---

## FASE 3 — Validación

### app_role post-corrección

| Rol | App | habilitada |
|---|---|---|
| Administrador | user | ✓ |
| Administrador | inventario | ✓ |
| Administrador | apps | ✓ |
| Rector | user | ✓ |
| Rector | inventario | ✓ |
| Rector | apps | ✓ |
| Coordinador | user | ✓ |
| Coordinador | inventario | ✓ |
| Auxiliar | inventario | ✓ |
| Docente | inventario | ✓ |

### App::visiblesPara(rectoriaiee)

```
[user]       Usuarios
[inventario] Inventario
[apps]       Aplicaciones
Total: 3
```

---

## Validaciones

| ID | Validación | Estado |
|---|---|---|
| V-001 | Usuario encontrado | ✓ id=54 |
| V-002 | Roles correctamente asignados | ✓ Rector con 77 permisos |
| V-003 | Permisos correctamente asignados | ✓ 77/77 permisos activos |
| V-004 | Acceso a Users (`/iee/users/users`) | ✓ visiblesPara incluye 'user' |
| V-005 | Acceso a Inventario (`/iee/inventario/bienes`) | ✓ visiblesPara incluye 'inventario' |
| V-006 | Acceso a Dependencias | ✓ permiso ver-dependencias + app inventario |
| V-007 | Acceso a Responsables | ✓ permiso ver-responsables-bienes + app inventario |
| V-008 | Sin regresiones para otros roles | ✓ Coordinador/Auxiliar/Docente mantienen acceso correcto |

---

## Versiones

| Componente | Antes | Después |
|---|---|---|
| IEE | v1.14.0 | **v1.14.1** |
| BhagamApps | v1.14.0 | **v1.14.1** |
| Apps | v1.5.0 | **v1.5.1** |

---

## Archivos modificados

```
Modules/Apps/database/migrations/2026_06_11_200000_assign_app_roles_rbac_recovery.php  [NEW]
config/versiones.php
CHANGELOG.md
VERSIONING.md
docs/changelog/iee.md
docs/changelog/bhagamapps.md
docs/changelog/apps.md
docs/impl/HOTFIX-RBAC-001-Recuperacion-Acceso-Administrador.md  [NEW]
```
