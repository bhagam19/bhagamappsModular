# IMPL-INFRA-001 — Public Alias Migration: /Modular → /iee

**Fecha:** 2026-06-10
**Versión:** IEE v1.12.1 | BhagamApps v1.12.1
**Estado:** Completado

---

## Contexto

IMPL-CORE-BRANDING-001 (v1.12.0) actualizó `APP_URL`, `ASSET_URL` y `SESSION_PATH` a `/IEE`
sin crear el symlink correspondiente en el servidor, provocando 404 en assets y rutas (HOTFIX-CORE-001).

HOTFIX-CORE-001 restauró la operatividad revirtiendo a `/Modular`.

Esta implementación completa la migración de forma controlada.

---

## Infraestructura previa

```
public_html/public/
  Modular -> /home/adolfo/web/bhagamapps.com/private/bhagamappsModular/public
```

`.env`:
```
APP_URL=http://bhagamapps.com/Modular
ASSET_URL=http://bhagamapps.com/Modular
SESSION_PATH=/Modular
```

---

## Cambios aplicados

### 1. Symlink creado

```bash
ln -s /home/adolfo/web/bhagamapps.com/private/bhagamappsModular/public \
      /home/adolfo/web/bhagamapps.com/public_html/public/iee
```

Resultado en `public_html/public/`:
```
Modular -> .../bhagamappsModular/public   (conservado)
iee     -> .../bhagamappsModular/public   (nuevo)
```

### 2. `.env` actualizado

```diff
- APP_URL=http://bhagamapps.com/Modular
- ASSET_URL=http://bhagamapps.com/Modular
- SESSION_PATH=/Modular
+ APP_URL=http://bhagamapps.com/iee
+ ASSET_URL=http://bhagamapps.com/iee
+ SESSION_PATH=/iee
```

### 3. Caches limpiadas

```
config:clear / cache:clear / route:clear / view:clear
```

---

## Validaciones

| ID    | Validación                                  | Resultado |
|-------|---------------------------------------------|-----------|
| V-001 | `/Modular/login` operativo                  | 200 OK    |
| V-001 | `/Modular` CSS AdminLTE operativo           | 200 OK    |
| V-002 | `/iee` symlink creado y sirve archivos      | OK        |
| V-003 | `/iee/login` HTTP                           | 200 OK    |
| V-004 | `url('/home')` genera `/iee/home`           | OK        |
| V-005 | `/iee/inventario/bienes` (redirect a login) | 200 OK    |
| V-005 | `/iee/users/users` (redirect a login)       | 200 OK    |
| V-006 | CSS AdminLTE via `/iee`                     | 200 OK    |
| V-006 | JS AdminLTE via `/iee`                      | 200 OK    |
| V-006 | Build CSS via `/iee`                        | 200 OK    |
| V-006 | Login HTML referencia assets en `/iee`      | OK        |
| V-007 | Livewire min.js via `/iee`                  | 200 OK    |
| V-007 | Livewire update endpoint (419 sin CSRF)     | Correcto  |

---

## Política de transición

`/Modular` se mantiene operativo de forma indefinida hasta al menos IEE v1.14.0.
No eliminar el symlink hasta decisión explícita de PMO.

**Justificación:** reducir riesgo de rotura en favoritos, enlaces guardados y documentación histórica.

---

## URL canónica del producto

```
http://bhagamapps.com/iee
```
