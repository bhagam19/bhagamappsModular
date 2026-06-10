# IMPL-INV-QA-001 — Inventory Test Foundation

**Fecha:** 2026-06-10
**Versión Inventario:** v2.10.5
**Versión BhagamApps:** v1.11.5
**SHA commit:** (ver git log)
**Estado:** COMPLETADO ✓

---

## Objetivo

Crear la primera suite formal de tests automatizados para el módulo Inventario usando
PHPUnit 11. Cubrir autorización, flujos críticos de negocio y protección de regresiones
documentadas.

---

## Estructura implementada

```
tests/Feature/Inventario/
├── InventarioTestCase.php         # Base abstract — DatabaseTransactions + helpers
├── PermissionsTest.php            # Fase 1: 17 tests de autorización
├── BienesTest.php                 # Fase 2+3: Bienes + GAP-001/002
├── NotificacionesTest.php         # Fase 3: IMPL-INV-NOTIF-001B regressions
├── HistorialUbicacionesTest.php   # Fase 2+3: IMPL-INV-005 + GAP-002
└── ResponsablesTest.php           # Fase 2: bienes_responsables CRUD
```

**Resultado final:** 50 tests / 73 assertions — todos verdes.

---

## Decisiones técnicas

### DatabaseTransactions, no RefreshDatabase

Sin `.env.testing` ni SQLite configurado, `RefreshDatabase` destruiría la base MySQL de
desarrollo. `DatabaseTransactions` envuelve cada test en una transacción que se revierte
al finalizar — cero daño colateral.

### APP_URL=http://localhost en phpunit.xml

**Raíz del bloqueante 404:** `APP_URL=http://bhagamapps.com/Modular` en `.env` hace que
`url('/inventario/bienes')` genere `http://bhagamapps.com/Modular/inventario/bienes`.
La capa de testing usa `prepareUrlForRequest()` que llama a `url()`, produciendo un path
`/Modular/inventario/bienes` que no coincide con la ruta registrada `inventario/bienes`.

**Fix:** `<env name="APP_URL" value="http://localhost"/>` en `phpunit.xml`. Los 32 routes
de Inventario estaban registrados correctamente; sólo el path matching fallaba.

### Guard de autenticación: Modules\User\Entities\User

El módulo usa un guard custom en `config/auth.php`. Los helpers `crearAdmin()` y
`crearUsuarioConRol()` instancian `Modules\User\Entities\User` directamente para
garantizar compatibilidad con `actingAs()` y `Livewire::actingAs()`.

### Dependencias reales de BD (sin mocks)

Los tests asumen una BD de desarrollo con datos reales:
- Roles existentes: Administrador, Rector, Coordinador, Docente
- Al menos una `Dependencia` para tests de HMB y NotificacionesTest
- Permisos ya asignados a roles (tabla `permission_role`)

Tests que requieren ubicaciones/dependencias extras usan `markTestSkipped()` si los
datos no están disponibles.

---

## Cobertura por archivo

### PermissionsTest.php (17 tests)

| Test | Verificación |
|------|-------------|
| `test_invitado_es_redirigido_a_login_*` (×3) | Middleware `auth` → 302 a `/login` |
| `test_usuario_sin_acceso_inventario_*` (×2) | Middleware `app.access:inventario` → 403 |
| `test_coordinador_puede_ver_bienes` | Permiso `ver-bienes` para Coordinador |
| `test_administrador/rector_puede_ver_bienes` (×2) | Permiso `ver-bienes` para Admin/Rector |
| `test_coordinador_no_puede_ver_hmb` | Permiso `gestionar-historial-modificaciones-bienes` no asignado a Coordinador |
| `test_administrador/rector_puede_ver_hmb` (×2) | Permiso HMB para Admin/Rector |
| `test_coordinador_no_puede_ver_heb` | Permiso HEB no asignado a Coordinador |
| `test_administrador_puede_ver_heb` | Permiso HEB para Admin |
| `test_administrador/coordinador_puede_ver_responsables` (×2) | Permiso `ver-responsables-bienes` |
| `test_administrador/coordinador_puede_ver_historial_ubicaciones` (×2) | Permiso `ver-historial-ubicaciones-bienes` |

### BienesTest.php (7 tests)

- Render de `BienesIndex` sin errores
- GAP-001: tabla `bienes` no tiene `user_id`
- GAP-002: tabla `bienes` no tiene `ubicacion_id`
- Creación vía `BienesIndex::store()` con `nombreSeleccionado`+`origenSeleccionado`
- Bien en BD tras `crearBien()`
- Edición directa por Admin vía `EditarCampoBien::actualizar()`
- Solicitud de modificación genera HMB pendiente

### NotificacionesTest.php (9 tests)

- `aprobarCambio()` actualiza estado a `aprobada` sin eliminar (D-1)
- `rechazarCambio()` actualiza estado a `rechazada` sin eliminar (D-6)
- `aprobarCambio()` actualiza el campo del bien en BD
- Dropdown renderiza solo pendientes
- Icono renderiza sin errores
- Icono reacciona al evento `cambioActualizado` sin wire:poll (IMPL-INV-008)
- No existe `wire:poll` en vistas de icono y dropdown

### HistorialUbicacionesTest.php (8 tests)

- GAP-002 regresión: `bienes` no tiene `ubicacion_id`
- Bien sin historial → `$bien->ubicacionActual` es null
- Bien con un registro → `ubicacionActual->ubicacion_destino_id` correcto
- Múltiples registros → `ubicacionActual` retorna el más reciente (`latest('fecha_movimiento')`)
- Cambio de ubicación crea registro en `historial_ubicaciones_bienes`
- Render de `HistorialUbicacionesBien` sin errores
- Tabla `historial_ubicaciones_bienes` existe
- Columnas requeridas: `bien_id`, `ubicacion_destino_id`, `fecha_movimiento`

### ResponsablesTest.php (9 tests)

- Render de `ResponsablesIndex` sin errores
- Tabla `bienes_responsables` existe con columnas requeridas
- Asignación de responsable crea registro con `fecha_asignacion`
- Asignación vía Livewire (`iniciarAsignacion` + `confirmarAsignacion`)
- Retiro de responsable registra `fecha_retiro`
- Transferencia: anterior con `fecha_retiro`, nuevo sin ella
- Bien recién creado sin responsables activos

---

## Correcciones aplicadas durante implementación

| Issue | Causa | Fix |
|-------|-------|-----|
| Todos los tests HTTP → 404 | `APP_URL` con subfolder `/Modular` confunde `url()` en tests | `APP_URL=http://localhost` en `phpunit.xml` |
| `->call('crearBien')` | Método real es `store()` en `BienesIndex` | Cambiado a `->call('store')` |
| `->test(EditarCampoBien, ['bien' => $bien])` | Mount espera `int $bienId, string $campo` | Cambiado a `['bienId' => $bien->id, 'campo' => 'nombre']` |
| `->call('solicitarCambio')` | Método real es `actualizar()` | Cambiado a `->call('actualizar')` |
| `->set('nombre', ...)` en store test | Store usa `nombreSeleccionado` (combobox) | Cambiado a `->set('nombreSeleccionado', ...)` + `origenSeleccionado` |
| `$bien->ubicacionActual()` | Retorna `HasOne` relation, no el modelo | Cambiado a `$bien->ubicacionActual` (property) |
| `assertDatabaseHas(..., 'mensaje')` | 3er param es `$connection`, no mensaje | Eliminado 3er argumento |
| Timestamp tie-break en historial ubicaciones | Dos registros creados en mismo segundo | Añadido `fecha_movimiento: now()->subMinute()` al primero |

---

## Validaciones V-001 a V-005

| V-ID | Verificación | Estado |
|------|-------------|--------|
| V-001 | Suite ejecuta sin errores fatales | ✓ OK |
| V-002 | Tests de autorización prueban los 3 roles con acceso | ✓ 17 tests pasan |
| V-003 | Tests de negocio cubren HMB aprobar/rechazar | ✓ 9 tests NotificacionesTest |
| V-004 | Regresiones GAP-001/002 e IMPL-INV-005 protegidas | ✓ 8 tests HistorialUbicaciones |
| V-005 | DatabaseTransactions — no se destruye la BD de desarrollo | ✓ Verificado |
