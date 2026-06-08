# IMPL-001 — Correcciones Críticas (Fase 0)

**Fecha:** 2026-06-08
**Referencia:** Auditoría Integral BhagamAppsModular — Hallazgos P0
**Estado:** Completado

---

## Resumen

Corrección de 6 grupos de bugs activos que impedían el funcionamiento correcto de la
aplicación en producción o representaban vulnerabilidades de acceso críticas.

---

## Fix 1 — DB facade faltante en Notificaciones.php

**Hallazgo:** `aprobarCambio()` llamaba `DB::beginTransaction()` sin importar la facade.
Toda aprobación de cambios lanzaba `Error: Class "DB" not found`.

**Archivo:** `Modules/Inventario/Livewire/Notifications/Notificaciones.php`

**Solución:** Agregado `use Illuminate\Support\Facades\DB;` al bloque de imports.

**Impacto:** El flujo completo de aprobación de modificaciones de bienes ahora funciona.

---

## Fix 2 — Gates aprobar/rechazar-cambios-bienes no definidos

**Hallazgo:** `Notificaciones` llamaba `$this->authorize('aprobar-cambios-bienes')` y
`$this->authorize('rechazar-cambios-bienes')`, pero ninguno de los dos gates estaba
registrado en `AuthServiceProvider`. Cada llamada lanzaba `AuthorizationException`.

**Archivo:** `app/Providers/AuthServiceProvider.php`

**Solución:** Definidos ambos gates delegando al permiso `aprobar-pendientes-bienes`,
que ya existe en la BD y está asignado a los roles Administrador y Rector.

```php
Gate::define('aprobar-cambios-bienes', function ($user) {
    return $user->hasPermission('aprobar-pendientes-bienes');
});

Gate::define('rechazar-cambios-bienes', function ($user) {
    return $user->hasPermission('aprobar-pendientes-bienes');
});
```

**Impacto:** Los flujos de aprobación y rechazo de modificaciones ahora funcionan
y solo los usuarios con permiso `aprobar-pendientes-bienes` pueden ejecutarlos.

---

## Fix 3 — CreateNewUser usaba clase inexistente

**Hallazgo:** `use App\Models\User;` importaba una clase que no existe en el proyecto.
El modelo de usuario está en `Modules\User\Entities\User`. El registro de nuevos
usuarios fallaba con `Class "App\Models\User" not found`.

**Archivo:** `app/Actions/Fortify/CreateNewUser.php`

**Solución:** Cambiado el import a `use Modules\User\Entities\User;`.

**Impacto:** El registro de nuevos usuarios desde el formulario público funciona.

---

## Fix 4 — Bug en campo origen al crear un bien

**Hallazgo:** `BienesIndex::store()` asignaba `$this->origen` (propiedad pública vacía)
en lugar de `$origenFinal` (variable local con el valor procesado del formulario).
El campo `origen` siempre se guardaba como `null` en todos los bienes nuevos.

**Archivo:** `Modules/Inventario/Livewire/Bienes/BienesIndex.php` (línea 246)

**Solución:**
```php
// Antes:
'origen' => $this->origen,
// Después:
'origen' => $origenFinal,
```

**Impacto:** El campo origen se almacena correctamente al crear un bien.

---

## Fix 5 — HomeController consultaba incorrectamente las apps del usuario

**Hallazgo:** `App::where('user_id', auth()->id())->get()` consultaba la columna
`apps.user_id` que corresponde al creador del registro (siempre el usuario 1 — admin),
no al usuario con acceso. La relación real es many-to-many a través de la tabla pivot
`app_user`. Ningún usuario veía sus aplicaciones en el dashboard.

**Archivo:** `app/Http/Controllers/Ppal/HomeController.php`

**Solución:**
```php
// Antes:
$apps = App::where('user_id', auth()->id())->get();
// Después:
$apps = auth()->user()->apps()->wherePivot('activo', true)->where('habilitada', true)->get();
```

**Impacto:** El dashboard muestra únicamente las aplicaciones asignadas al usuario
autenticado que están activas en el pivot y habilitadas globalmente.

---

## Fix 6 — Componentes Editar*User sin verificación de permisos

**Hallazgo:** `EditarApellidosUser`, `EditarEmailUser`, `EditarRolUser` y
`EditarUserIDUser` no verificaban permisos. Cualquier usuario autenticado podía
activar el modo edición y guardar cambios sobre cualquier usuario, incluyendo
cambios de rol.

**Archivos:**
- `Modules/User/Livewire/User/EditarApellidosUser.php`
- `Modules/User/Livewire/User/EditarEmailUser.php`
- `Modules/User/Livewire/User/EditarRolUser.php`
- `Modules/User/Livewire/User/EditarUserIDUser.php`

**Solución:** Agregada verificación del permiso `editar-usuarios` en ambos métodos
`editar()` (activa el modo inline) y `guardar()` (persiste el cambio). El patrón
aplicado es consistente con `EditarNombresUser` ya existente:

```php
public function editar()
{
    if (!auth()->user()?->hasPermission('editar-usuarios')) {
        abort(403);
    }
    $this->editando = true;
}

public function guardar()
{
    abort_unless(auth()->user()?->hasPermission('editar-usuarios'), 403);
    // ... persiste el cambio
}
```

**Impacto:** Solo usuarios con permiso `editar-usuarios` (Administrador, Rector por
defecto) pueden modificar datos de otros usuarios. Escalada de privilegios eliminada.

---

## Archivos modificados

| Archivo | Tipo de cambio |
|---|---|
| `Modules/Inventario/Livewire/Notifications/Notificaciones.php` | Import añadido |
| `app/Providers/AuthServiceProvider.php` | 2 gates añadidos |
| `app/Actions/Fortify/CreateNewUser.php` | Import corregido |
| `Modules/Inventario/Livewire/Bienes/BienesIndex.php` | Variable corregida |
| `app/Http/Controllers/Ppal/HomeController.php` | Query corregida |
| `Modules/User/Livewire/User/EditarApellidosUser.php` | Autorización añadida |
| `Modules/User/Livewire/User/EditarEmailUser.php` | Autorización añadida |
| `Modules/User/Livewire/User/EditarRolUser.php` | Autorización añadida |
| `Modules/User/Livewire/User/EditarUserIDUser.php` | Autorización añadida |

**Total archivos modificados:** 9
**Líneas cambiadas:** ~40

---

## Pendiente (fuera de alcance de esta fase)

- Registro abierto a roles privilegiados (role_id 2 y 3 en formulario público) — Fase 1
- SSL no habilitado en servidor — Fase 1
- CheckPermission middleware sin registrar — Fase 1
