# IMPL-INV-006 — Mantenimientos Programados de Bienes

**Estado:** COMPLETADO  
**Versión:** Inventario v2.10.0 | BhagamApps v1.11.0  
**Fecha:** 2026-06-10  
**Módulo:** `Modules/Inventario`

---

## Objetivo

Implementar la gestión completa de mantenimientos programados para bienes:
programación, edición, completar (realizado), cancelar, historial filtrable,
permisos RBAC, acceso desde sidebar Inventario.

---

## Diagnóstico de infraestructura preexistente

| Elemento | Estado al inicio |
|---|---|
| Tabla `mantenimientos_programados` | **Migrada** (2025-05-21) |
| Entidad `MantenimientoProgramado` | Existía — `$fillable` incompleto (faltaban `user_id`, `tipo`, `titulo`, `fecha_realizada`) |
| Relación `Bien::mantenimientosProgramados()` | **Ya existía** en `Bien.php` |
| Gate `ver-mantenimientos-programados` | **Ya definido** en `AuthServiceProvider` (solo este) |
| Seeder `MantenimientosProgramadosSeeder` | Existía |
| Livewire component | **Faltaba** |
| Ruta HTTP | **Faltaba** |
| Permisos RBAC seeded | **Faltaban** (4 permisos) |
| Sidebar entry | **Faltaba** |

---

## Archivos creados / modificados

### Modificados

| Archivo | Cambio |
|---|---|
| `Modules/Inventario/Entities/MantenimientoProgramado.php` | `$fillable` completado, casts de fecha, relación `user()`, método `esPendiente()` |
| `app/Providers/AuthServiceProvider.php` | 4 Gates `*-mantenimientos-programados` (loop foreach) |
| `Modules/Inventario/routes/web.php` | Ruta `GET /inventario/mantenimientos/programados` |
| `config/adminlte.php` | Entrada sidebar "Mantenimientos" |
| `config/versiones.php` | Inventario 2.9.0→2.10.0, BhagamApps 1.10.0→1.11.0 |
| `CHANGELOG.md` | v1.11.0 |
| `VERSIONING.md` | Tabla versiones actuales |
| `docs/changelog/inventario.md` | v2.10.0 |
| `docs/changelog/bhagamapps.md` | v1.11.0 |

### Creados

| Archivo | Propósito |
|---|---|
| `Modules/Inventario/Livewire/Mantenimientos/MantenimientosProgramadosIndex.php` | Componente Livewire CRUD principal |
| `Modules/Inventario/resources/views/livewire/mantenimientos/mantenimientos-programados-index.blade.php` | Vista Livewire con tabla, paneles inline |
| `Modules/Inventario/Http/Controllers/MantenimientosProgramadosController.php` | Controlador delgado |
| `Modules/Inventario/resources/views/mantenimientos/index.blade.php` | Vista página (wraps Livewire) |
| `Modules/Inventario/Database/Migrations/2026_06_10_000003_add_mantenimientos_programados_permissions.php` | 4 permisos RBAC |
| `docs/impl/IMPL-INV-006-Mantenimientos-Programados.md` | Este documento |

---

## Permisos RBAC

| Permiso (slug) | Administrador | Rector | Coordinador |
|---|---|---|---|
| `ver-mantenimientos-programados` | ✓ | ✓ | ✓ |
| `crear-mantenimientos-programados` | ✓ | ✓ | ✓ |
| `editar-mantenimientos-programados` | ✓ | ✓ | — |
| `cancelar-mantenimientos-programados` | ✓ | ✓ | — |

---

## Lógica funcional del Livewire

### Flujo de creación
1. Usuario con `crear-mantenimientos-programados` hace clic en "Programar"
2. Se abre panel de formulario inline (bien, tipo, título, descripción, fecha_programada)
3. `guardar()` valida y crea `MantenimientoProgramado` con `estado='pendiente'` y `user_id=auth()->id()`

### Flujo de edición
1. Solo disponible si `$reg->estado === 'pendiente'`
2. Botón "editar" → `iniciarEdicion($id)` → fila de edición inline
3. `guardarEdicion()` actualiza tipo, título, descripción, fecha_programada

### Flujo de completar (realizado)
1. Solo disponible si `estado === 'pendiente'`
2. `iniciarRealizado($id)` → panel "Marcar como Realizado" con campo fecha_realizada
3. `confirmarRealizado()` actualiza `estado='realizado'` y guarda `fecha_realizada`

### Flujo de cancelar
1. Solo disponible si `estado === 'pendiente'`
2. Confirmación JS → `cancelarMantenimiento($id)` → `estado='cancelado'`

---

## Evidencia de validaciones

```
# Migración ejecutada
2026_06_10_000003_add_mantenimientos_programados_permissions .. 31.61ms DONE

# Entidad fillable
bien_id, user_id, tipo, titulo, descripcion, fecha_programada, fecha_realizada, estado

# Permisos seeded en BD
cancelar-mantenimientos-programados: Administrador, Rector
crear-mantenimientos-programados: Administrador, Coordinador, Rector
editar-mantenimientos-programados: Administrador, Rector
ver-mantenimientos-programados: Administrador, Coordinador, Rector

# Ruta registrada
GET|HEAD  inventario/mantenimientos/programados  inventario.mantenimientos.programados
```

---

## Arquitectura aplicada

- Controlador delgado (thin controller) — solo retorna vista
- Livewire para toda la lógica de UI y operaciones CRUD
- Permisos verificados con `abort_unless()` en cada método de escritura
- `@can` directivas en vistas para condicionar botones
- `insertOrIgnore` en migración de permisos (idempotente)
- Gates en `AuthServiceProvider` siguiendo patrón foreach de IMPL-INV-002/003/005
