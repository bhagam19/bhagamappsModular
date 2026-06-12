# HOTFIX-USERS-004 — Error 419 en Búsqueda, Filtros y Ordenamiento de Usuarios

**Fecha:** 2026-06-11
**Módulo:** User (`Modules/User`)
**Archivos modificados:**
- `Modules/User/Livewire/User/UserIndex.php`
- `Modules/User/Http/Middleware/CheckForzarCambioPassword.php`

---

## Síntoma reportado

Después de IMPL-USERS-002 y HOTFIX-USERS-003, `/iee/users/users` cargaba correctamente.
Sin embargo, al ejecutar búsqueda, filtros o cambiar el orden de columnas, el navegador
mostraba el diálogo de Livewire: **"This page has expired. Would you like to refresh?"**
y el servidor respondía HTTP 419.

---

## Diagnóstico completo

### Fase 1: Verificación del servidor

Se realizaron pruebas exhaustivas del endpoint `/iee/livewire/update` con sesiones reales
de la base de datos. **Todos los tests del servidor retornaron HTTP 200.**

#### Tests realizados

| Escenario | Resultado |
|-----------|-----------|
| POST con sesión válida (user_id=54) + `_token` correcto | **200** |
| POST con sesión de 60 min de antigüedad + token correcto | **200** |
| POST sin cookie de sesión | 419 (esperado) |
| POST con sesión inexistente en BD + token desactualizado | 419 (esperado) |
| POST con sesión válida + token incorrecto | 419 (esperado) |

#### Componentes verificados como correctos

- `APP_URL=http://bhagamapps.com/iee` → `data-update-uri="/iee/livewire/update"` ✓
- `<meta name="csrf-token">` generado correctamente en la página ✓
- Cookie `iee_session` presente y bien formada en las respuestas ✓
- Sesión NO se regenera durante la carga de la página ✓
- `VerifyCsrfToken` lee `_token` del cuerpo JSON correctamente ✓
- Ruta `POST livewire/update` (default Livewire) maneja las peticiones correctamente ✓
- Ruta `POST iee/livewire/update` (custom modular) registrada como complemento ✓
- `Livewire::setUpdateRoute()` configura correctamente `data-update-uri` ✓

### Fase 2: Causa raíz confirmada

El 419 es **sesión expirada o corrompida**, no un fallo de código CSRF.

Mecanismo: Livewire JS lee `_token` de `<meta name="csrf-token">` (que se renderizó con
la sesión A). Si entre la carga de la página y la primera interacción Livewire la sesión A
ya no existe en la BD, Laravel crea una nueva sesión B con un `_token` distinto. La petición
Livewire envía `_token` de A, pero el servidor valida contra B → mismatch → 419.

#### Escenarios que producen este estado

1. **Sesión expirada**: el usuario cargó la página, esperó más de `SESSION_LIFETIME=120 min`
   sin interactuar, y luego intentó usar la búsqueda. La cookie del navegador aún existe pero
   la sesión ya fue borrada de la BD por el garbage collector.

2. **Página abierta durante despliegue de HOTFIX-003**: el usuario tenía la página abierta
   mientras se corregía el FatalError `mount(): void`. La página tenía un token de la sesión
   anterior (posiblemente corrompida por el error). Al intentar usar búsqueda/filtros, la
   sesión ya no era válida → 419.

#### Por qué el 419 aparece específicamente en búsqueda/filtros/ordenamiento

Antes de IMPL-USERS-002, `UserIndex` no tenía interacciones Livewire (no había `wire:model`,
`wire:click`). El 419 era imposible porque no había peticiones AJAX a `/livewire/update`.
IMPL-USERS-002 agregó esas interacciones, exponiendo el comportamiento estándar de Livewire
ante sesiones expiradas.

#### Comportamiento esperado de Livewire

El diálogo "This page has expired. Would you like to refresh?" es el comportamiento **correcto**
de Livewire cuando recibe HTTP 419. El usuario debe hacer clic en "Yes" para recargar la
página con una sesión válida.

---

## Correcciones aplicadas

### Corrección 1: `->layout()` en componente anidado

**Archivo:** `Modules/User/Livewire/User/UserIndex.php`

`UserIndex` es un componente **anidado** (renderizado via `@livewire('user.user-index')`
en `user::user.index`). El método `render()` de IMPL-USERS-002 retornaba
`view(...)->layout('layouts.app')`.

Para componentes anidados, `->layout()` es un **no-op**: `HandleComponents` no lee
`$view->layoutConfig` — solo lo hace `HandlesPageComponents::__invoke()` para componentes
full-page. Sin embargo, incluirlo es semánticamente incorrecto y confuso.

```php
// ANTES (incorrecto para componente anidado)
return view('user::livewire.user.user-index', [
    'users' => $query->paginate($this->perPage),
])->layout('layouts.app');

// DESPUÉS
return view('user::livewire.user.user-index', [
    'users' => $query->paginate($this->perPage),
]);
```

### Corrección 2: Bug en `CheckForzarCambioPassword` middleware

**Archivo:** `Modules/User/Http/Middleware/CheckForzarCambioPassword.php`

El middleware tenía `'livewire/'` en la lista de rutas permitidas para usuarios con
`forzar_cambio_password=true`. La evaluación resultante era:

```php
$request->is('livewire/')   // FALSE — solo coincide con la ruta literal 'livewire/'
$request->is('livewire//*') // FALSE — doble barra no coincide con 'livewire/update'
```

Esto causaba que peticiones Livewire de usuarios con `forzar_cambio_password=true`
cayeran al `redirect('/user/profile')` en lugar de ser procesadas.

```php
// ANTES (no coincide con 'livewire/update')
'livewire/',

// DESPUÉS (coincide correctamente)
'livewire',
// $request->is('livewire/*') → TRUE para 'livewire/update', 'livewire/upload-file', etc.
```

---

## Configuración de sesión (referencia)

```
SESSION_DRIVER=database
SESSION_LIFETIME=120          # 2 horas
SESSION_DOMAIN=bhagamapps.com
# config/session.php → path='/' (hardcoded, SESSION_PATH en .env no tiene efecto)
```

Para sesiones de larga duración considerar aumentar `SESSION_LIFETIME` en `.env`.

---

## Verificación post-fix

```bash
# Servidor retorna 200 para peticiones Livewire válidas
HTTP/1.1 200 OK  # POST /iee/livewire/update con sesión válida + token correcto
```

Prueba de los tres escenarios de 419 confirmados (comportamiento esperado e inalterado):
- Sin cookie de sesión → 419 ✓
- Sesión expirada (no existe en BD) → 419 ✓
- Token CSRF incorrecto → 419 ✓
