# IMPL-INFRA-BACKUP-006 — Restauración de Snapshot Institucional desde CAB

**Estado:** COMPLETADO  
**Fecha:** 2026-06-13  
**Versiones:** IEE v1.23.9 · BhagamApps v1.22.9  
**SHA:** ver sección de cierre  
**Prerrequisitos:** IMPL-INFRA-BACKUP-001 ✓ · 002 ✓ · 003A ✓ · 003B ✓ · 004 ✓ · 005 ✓ · AUDIT-BACKUP-002 ✓ · AUDIT-BACKUP-004 ✓

---

## Objetivo

Permitir al Administrador Principal restaurar un Snapshot Institucional desde la interfaz
web CAB sin acceso SSH. Reutiliza el motor `backup:restore-from-zip` (IMPL-INFRA-BACKUP-004)
sin duplicar lógica.

---

## Archivos creados

| Archivo | Propósito |
|---|---|
| `Modules/AdminSistema/Livewire/Backups/RestaurarBackup.php` | Componente Livewire — máquina de estados |
| `Modules/AdminSistema/Http/Controllers/RestaurarController.php` | Controlador de la página |
| `Modules/AdminSistema/resources/views/backups/restaurar.blade.php` | Wrapper de página |
| `Modules/AdminSistema/resources/views/livewire/backups/restaurar-backup.blade.php` | Vista Livewire (4 estados) |

## Archivos modificados

| Archivo | Cambio |
|---|---|
| `Modules/AdminSistema/Database/Seeders/AdminSistemaSeeder.php` | Permiso `restaurar-backups` añadido |
| `Modules/AdminSistema/Routes/web.php` | Ruta `GET /admin/backups/restaurar` |
| `app/Providers/AuthServiceProvider.php` | Gate `restaurar-backups` con doble condición |
| `config/adminlte.php` | Ítem "Restaurar" en menú Administración del Sistema |
| `Modules/User/Database/Seeders/data/permissions.csv` | Entrada ID=84 |
| `Modules/User/Database/Seeders/data/permission_role.csv` | Entrada ID=185 |

---

## RESTORE-WEB-001 — Sección Restaurar en CAB

**Ruta:** `GET /admin/backups/restaurar` → `admin.backups.restaurar`  
**Middleware:** `web, auth, app.access:admin-sistema, permission:restaurar-backups`  
**Visible en menú:** solo si `Gate::check('restaurar-backups')` → true  
(requiere `hasPermission('restaurar-backups') && isAdminPrincipal()`)

La ruta está registrada **antes** de `GET /backups/{fecha}` en Routes/web.php para
evitar que 'restaurar' sea capturado por el wildcard de fecha.

---

## RESTORE-WEB-002 — Listado de respaldos disponibles

`RestaurarBackup::cargarBackups()` llama a `BackupReaderService::listar()`, que lee
`glob(base_path('backups/IEE-????-??-??.zip'))` y enriquece con `metadata.json`.

Columnas en la tabla: fecha · versión IEE · usuarios · bienes · tamaño · estado · acción.

Solo se muestra el botón "Restaurar" si el backup tiene metadata válida.

---

## RESTORE-WEB-003 — Vista previa del snapshot

Al seleccionar un backup, el componente transiciona a `estado = 'vista-previa'` y muestra:

- Snapshot: `IEE-YYYY-MM-DD.zip`
- Fecha de generación, entorno, base de datos
- Versión IEE, versión BhagamApps
- Tablas exportadas, total de registros
- Conteos destacados: usuarios, bienes, dependencias, categorías, permisos, roles

El administrador puede revisar la información antes de continuar.

---

## RESTORE-WEB-004 — Doble confirmación obligatoria

El flujo de confirmación tiene dos pasos:

1. **Vista previa** → botón "Continuar con la restauración" → transiciona a `confirmar`
2. **Confirmar** → campo de texto donde el usuario debe escribir exactamente `RESTAURAR`
   (validación visual inline: verde si correcto, rojo si incorrecto)

El botón "Ejecutar restauración" permanece `disabled` hasta que `$confirmacion === 'RESTAURAR'`.
La validación se hace en `ejecutarRestauracion()` antes de cualquier acción:

```php
if (trim($this->confirmacion) !== 'RESTAURAR') {
    return;
}
```

---

## RESTORE-WEB-005 — Protección Administrador Principal

Tres capas de verificación:

1. **Gate** (`AuthServiceProvider`): `hasPermission('restaurar-backups') && isAdminPrincipal()`
2. **Middleware** de ruta: `permission:restaurar-backups` (requiere el permiso)
3. **Controlador** `RestaurarController::index()`: `abort(403)` si `!isAdminPrincipal()`
4. **Livewire** `autorizar()` en `mount()` y en cada método de acción: `abort(403)` si no cumple

Un usuario Administrador sin `es_principal = true` no puede ver el menú, acceder a la ruta,
ni ejecutar ninguna acción del componente Livewire.

---

## RESTORE-WEB-006 — Reutilización de backup:restore-from-zip

```php
$exitCode = Artisan::call('backup:restore-from-zip', [
    '--file'  => "backups/IEE-{$this->fechaSeleccionada}.zip",
    '--force' => true,
]);
$this->outputComando = Artisan::output();
```

- `--force`: omite la confirmación interactiva del CLI (ya manejada en la UI web)
- `Artisan::output()`: captura toda la salida del comando para mostrarla en la UI
- No se duplica lógica de extracción, sincronización de CSVs, transacción DB ni validación

---

## RESTORE-WEB-007 — Registro de auditoría

Cada intento de restauración (exitoso o fallido) genera una entrada JSONL en
`storage/logs/restore.log`:

```json
{
  "fecha": "2026-06-13 17:54:40",
  "origen": "CAB-WEB",
  "usuario_id": 54,
  "usuario": "Adolfo León Ruiz Hernández",
  "backup": "IEE-2026-06-13.zip",
  "resultado": "EXITOSA",
  "detalle": "EXITOSA desde CAB (exit=0)"
}
```

El campo `origen: CAB-WEB` distingue las restauraciones web de las CLI
(que también escriben en el mismo log sin campo `origen`).

---

## RESTORE-WEB-008 — Resultado visible

Después de la restauración, el componente muestra:

**Éxito:**
```
✓ Restauración completada exitosamente
El Snapshot IEE-YYYY-MM-DD.zip fue restaurado correctamente.
```

**Fallo:**
```
✗ Restauración fallida
Ocurrió un error durante la restauración. Revisa el detalle a continuación.
```

En ambos casos, la salida completa del comando se muestra en un bloque `<pre>` con
estilo terminal (fondo oscuro, fuente monospace).

---

## RESTORE-WEB-009 — Permiso restaurar-backups

| Campo | Valor |
|---|---|
| ID en BD | 84 |
| Slug | `restaurar-backups` |
| Nombre | `restaurar backups` |
| Descripción | Permite restaurar la base de datos institucional desde un Snapshot ZIP. Requiere es_principal = true. |
| Categoría | `admin-sistema` |
| Rol asignado | Administrador (role_id=1) |

Gate registrado en `AuthServiceProvider`:
```php
Gate::define('restaurar-backups', fn($user) =>
    $user->hasPermission('restaurar-backups') && $user->isAdminPrincipal()
);
```

---

## RESTORE-WEB-010 — Restricciones de seguridad

| Restricción | Implementación |
|---|---|
| NO ejecutar automáticamente | No hay cron ni Job scheduler que dispare restauraciones |
| NO programar restauraciones | No existe endpoint de scheduling |
| NO exponer migrate:fresh | El comando nunca se llama; la restauración usa el motor existente |
| NO permitir restaurar desde URL | El ZIP se construye desde `backups/IEE-{fecha}.zip` donde `fecha` se valida por regex `/^\d{4}-\d{2}-\d{2}$/` |
| NO permitir archivos externos | La ruta del ZIP se deriva de `$fechaSeleccionada` que viene del listado de la BD, nunca de input libre |
| Solo respaldos registrados por CAB | `BackupReaderService::listar()` lee únicamente los ZIPs del directorio `backups/` del servidor |

---

## Validaciones ejecutadas

| Validación | Resultado |
|---|---|
| V-001 Listado correcto | ✅ 1 backup(s) listado con fecha y metadata |
| V-002 Metadata visible | ✅ 3,451 registros · versiones · conteos |
| V-003 Confirmación doble funcional | ✅ `irAConfirmar()` + check `=== 'RESTAURAR'` |
| V-004 Solo Administrador Principal | ✅ Gate + middleware + controller + component |
| V-005 Reutiliza backup:restore-from-zip | ✅ `Artisan::call()` con `--force` |
| V-006 Auditoría creada | ✅ JSONL en `storage/logs/restore.log` |
| V-007 Sin regresiones | ✅ 4 rutas de backup funcionando, Gate OK |
| V-008 PHP lint limpio | ✅ 5 archivos PHP sin errores de sintaxis |

---

## Macroarquitectura del ciclo DR

```
Generación (CAB)
  BackupDashboard → GenerarBackupJob → BackupExportSeeders
    → 23 CSV + metadata.json → IEE-{fecha}.zip → backups/

Listado (CAB)
  BackupReaderService::listar() ← glob(backups/IEE-*.zip)

Descarga (CAB)
  BackupsController::descargar() → response()->download()

Restauración (CAB) ← NUEVO EN IMPL-INFRA-BACKUP-006
  RestaurarBackup (Livewire)
    → Artisan::call('backup:restore-from-zip', --force)
      → BackupRestoreFromZip Command
        → extraer ZIP → sincronizar CSVs → InstitutionalRestoreSeeder
        → DB::transaction → validación post-restore → restore.log
```

---

## Respuestas explícitas

**¿Puede un Administrador restaurar completamente la plataforma sin SSH?**

SÍ. El Administrador Principal puede:
1. Ir a Administración del Sistema → Backups → Restaurar
2. Seleccionar un Snapshot del listado
3. Revisar la vista previa (registros, versiones, conteos)
4. Escribir `RESTAURAR` para confirmar
5. Ver el resultado y la salida completa del proceso

Todo desde el navegador. Sin CLI, sin SSH.

**¿Queda cerrado el ciclo completo de Disaster Recovery?**

SÍ. El ciclo DR institucional está ahora completo:

| Paso | Implementación | Estado |
|---|---|---|
| Generación automática (02:00 AM) | `backup:export-seeders` + Schedule | ✅ IMPL-001 |
| CAB — Dashboard y generación manual | BackupDashboard + GenerarBackupJob | ✅ IMPL-002 |
| Restauración por seeders validada | InstitutionalRestoreSeeder + DR tests | ✅ IMPL-003A/B |
| Restauración CLI certificada | `backup:restore-from-zip --force` | ✅ IMPL-004 |
| Sincronización Google Drive | DriveService + SincronizarDriveJob | ✅ IMPL-005 |
| **Restauración desde CAB (web)** | **RestaurarBackup Livewire** | ✅ **IMPL-006** |
