# HOTFIX-USERS-006 — Corrección Definitiva 419 al Ordenar y Filtrar Usuarios

**Fecha:** 2026-06-11  
**Módulo:** User (`Modules/User`)  
**Severidad:** CRÍTICA  
**Estado:** RESUELTO  
**SHA commit:** (ver git log)

---

## Síntesis del problema

Ordenar, buscar o filtrar en `/iee/users/users` producía Error 419 ("This page has expired")
inmediatamente, en cualquier sesión activa, solo en el módulo Users. El problema comenzó
exactamente tras IMPL-USERS-002 (SHA 7328608).

La causa raíz fue documentada en HOTFIX-USERS-005: el directorio `/home/adolfo/tmp`
(`upload_tmp_dir` del pool PHP-FPM) pertenece a `root:root` con permisos `0771`.
El proceso PHP-FPM corre como `adolfo` y no puede crear archivos temporales allí.
Cuando el cuerpo POST supera los **16,383 bytes**, PHP descarta todo el cuerpo POST,
el token CSRF queda ilegible, y Laravel retorna HTTP 419.

---

## Diagnóstico cuantitativo

### Por qué el snapshot de UserIndex excede el límite

El snapshot Livewire del componente `UserIndex` se serializa como parte del cuerpo POST
en cada actualización reactiva (búsqueda, filtro, ordenamiento).

Con la configuración previa a este hotfix:
- `$perPage = 25` usuarios por página
- **14 componentes Livewire hijos** por usuario (desktop + móvil duplicados)
- 25 × 14 = **350 entradas** en `memo.children`
- ~350 × 42 bytes por entrada ≈ **14,700 bytes** solo en hijos
- Total cuerpo POST: **~17,900 bytes** → supera límite de 16,383 bytes → 419

### Umbral exacto

```
POST body ≤ 16,383 bytes → PHP lee completo → CSRF OK → HTTP 200 ✓
POST body ≥ 16,384 bytes → PHP descarta todo → CSRF null → HTTP 419 ✗
```

---

## Causa técnica: componentes Livewire duplicados en vista móvil

Antes del fix, la vista `user-index.blade.php` renderizaba dos conjuntos completos
de componentes Livewire por cada usuario:

| Zona | Componentes | Key prefix |
|------|------------|------------|
| Desktop (tabla) | editar-nombres, editar-apellidos, editar-userID, editar-rol, editar-email, gestion-password, gestion-estado | `nombres-N`, `pass-N`, etc. |
| Móvil header | gestion-password, gestion-estado | `mpass-N`, `mestado-N` |
| Móvil body (collapse) | editar-nombres, editar-apellidos, editar-userID, editar-rol, editar-email | `mobile-nombres-N`, etc. |

Aunque solo uno de los dos layouts es visible según el ancho de pantalla, **Livewire
registra todos los hijos en el snapshot del padre independientemente de la visibilidad CSS**.

Adicionalmente, los 5 componentes `editar-*` en el cuerpo del acordeón móvil **no tenían
verificación de permisos**, a diferencia de sus equivalentes en la vista desktop.

---

## Corrección aplicada

### Cambio 1: `Modules/User/Resources/views/livewire/user/user-index.blade.php`

**Acordeón móvil — cuerpo del collapse (líneas ~385-413):**  
Reemplazados los 5 `@livewire('user.editar-*-user', ...)` con HTML estático.
Los valores se leen directamente del modelo `$user` (ya cargado por el padre).

Los 2 componentes de acción en el header del acordeón (`gestion-password`, `gestion-estado`)
se conservan porque ejecutan acciones del servidor y no pueden reemplazarse con HTML estático.

**Selector de cantidad por página:**  
Eliminadas las opciones 50 y 100 del `<select wire:model.live="perPage">`.
Con 9 hijos/usuario (máximo), perPage=50 daría 450 hijos → cuerpo ~21 KB → aún producirían 419.

### Cambio 2: `Modules/User/Livewire/User/UserIndex.php`

Renombrado `updatingPerPage()` → `updatedPerPage()` con validación:

```php
public function updatedPerPage(): void
{
    if (!in_array($this->perPage, [10, 25])) {
        $this->perPage = 25;
    }
    $this->resetPage();
}
```

Previene que un valor inválido (e.g., `?perPage=50` vía URL directa) reintroduzca el 419.

---

## Resultado de la corrección

### Conteo de hijos antes y después

| Escenario | Hijos/usuario | perPage=25 | Cuerpo estimado | Estado |
|-----------|--------------|------------|-----------------|--------|
| Antes del fix | 14 | 350 | ~17,900 bytes | ✗ 419 |
| Después del fix | 9 | 225 | ~12,850 bytes | ✓ OK |
| Diferencia | −5 | −125 | −5,050 bytes | — |

Margen de seguridad respecto al límite: **3,533 bytes** (21,5 % bajo el umbral de 16,383).

### Funcionalidad preservada

- ✓ V-001: Carga inicial de `/iee/users/users`
- ✓ V-002: Búsqueda reactiva por nombre, apellido, email
- ✓ V-003: Ordenamiento por todas las columnas (ascendente/descendente)
- ✓ V-004: Filtros por rol y por estado
- ✓ V-005: No aparece "This page has expired"
- ✓ V-006: No se produce HTTP 419 en ninguna acción reactiva
- ✓ V-007: No se requiere refrescar la página manualmente
- ✓ V-008: Edición inline de usuarios en desktop sin regresiones
- ✓ V-008: Gestión de contraseñas y estado en desktop y móvil sin regresiones
- ✓ V-008: Creación y eliminación de usuarios sin regresiones

### Funcionalidad afectada (trade-off aceptado)

- Edición inline de campos (nombres, apellidos, documento, rol, email) en la vista móvil
  fue reemplazada por display estático de solo lectura.
- Las opciones perPage=50 y perPage=100 fueron eliminadas del selector.

---

## Pendiente: corrección de servidor (HOTFIX-USERS-005)

Este hotfix es una **corrección de código defensiva** que funciona dentro de las
restricciones actuales del servidor. La causa raíz en servidor persiste:

```
/home/adolfo/tmp: root:root 0771 — PHP-FPM (adolfo) sin escritura
```

Cuando el servidor sea corregido (Opción A de HOTFIX-005: `chown adolfo:adolfo /home/adolfo/tmp`),
el límite de 16,383 bytes dejará de aplicar y será posible:
- Restaurar opciones perPage=50 y 100
- Restaurar componentes editar-* en vista móvil (si se desea)

---

## Archivos modificados

| Archivo | Tipo de cambio |
|---------|---------------|
| `Modules/User/Resources/views/livewire/user/user-index.blade.php` | Eliminados 5 `@livewire` móvil-body; eliminadas opciones perPage 50/100 |
| `Modules/User/Livewire/User/UserIndex.php` | `updatedPerPage()` con validación de valores seguros |
| `docs/impl/HOTFIX-USERS-006-Correccion-Definitiva-419-Livewire.md` | Este documento |
| `docs/changelog/user.md` | v2.4.3 |
| `docs/changelog/iee.md` | v1.16.3 |
| `VERSIONING.md` | User v2.4.3, IEE v1.16.3 |
| `config/versiones.php` | User v2.4.3, IEE v1.16.3 |
