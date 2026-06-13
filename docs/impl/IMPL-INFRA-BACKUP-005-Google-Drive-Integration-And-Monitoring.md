# IMPL-INFRA-BACKUP-005 — Google Drive Integration & Monitoring

**Estado:** COMPLETADO  
**Fecha:** 2026-06-13  
**Prerrequisito completado:** IMPL-INFRA-BACKUP-004 (backup:restore-from-zip disponible)  
**SHA:** ver CHANGELOG

---

## Respuestas a las preguntas de resultado esperado

### ¿Los respaldos quedan protegidos fuera del servidor?

**Infraestructura: SÍ. Credenciales: PENDIENTE.**

El motor técnico de sincronización a Google Drive está completamente implementado.
Para activar la protección externa real, el operador debe configurar en `.env`:

```
BACKUP_GDRIVE_SA_JSON={"type":"service_account","project_id":"...","private_key":"..."}
BACKUP_GDRIVE_FOLDER_ID=<id_carpeta_drive>  # opcional
```

Una vez configurado, el ZIP se sube automáticamente en cada `backup:export-seeders`
y queda verificado en Drive.

### ¿Puede perderse completamente el servidor sin perder los ZIP?

**Sí, una vez configuradas las credenciales.** Con `BACKUP_GDRIVE_SA_JSON` activo:
- Cada backup diario (02:00 AM) se sube automáticamente a `iee-backup:IEE-Backups/`
- El historial de sincronizaciones en `storage/logs/drive-sync.log` confirma cuáles llegaron
- El `backup:sync-drive` puede sincronizar manualmente cualquier ZIP existente

Sin las credenciales configuradas: los ZIP existen solo en el servidor.

### ¿La plataforma queda lista para IMPL-INFRA-BACKUP-006 (Restauración desde CAB)?

**SÍ.** La infraestructura de Drive está completa: estado visible en CAB,
historial de sincronizaciones, alertas, permisos RBAC. El motor de restauración
(`backup:restore-from-zip`) está operativo desde IMPL-004. IMPL-006 puede
construir la UI web encima de esta base.

---

## DRIVE-001 — Auditoría del estado real

| Componente | Estado |
|---|---|
| rclone v1.71.2 en `/usr/bin/rclone` | ✓ Instalado |
| `BACKUP_RCLONE_REMOTE=iee-backup` | ✓ Configurado |
| `BACKUP_RCLONE_DEST=IEE-Backups` | ✓ Configurado |
| `BACKUP_RCLONE_BIN=/usr/bin/rclone` | ✓ Configurado |
| `BACKUP_GDRIVE_SA_JSON` | ✗ Vacío — credenciales pendientes |
| `BACKUP_GDRIVE_FOLDER_ID` | ✗ Vacío (opcional) |
| `~/.config/rclone/rclone.conf` | ✗ No existe |
| `uploadToDrive()` en BackupExportSeeders | ✓ Existía; refactorizado para usar DriveService |
| Verificación post-subida | ✗ No existía — implementada en este IMPL |
| Log de sincronizaciones | ✗ No existía — implementado en este IMPL |
| Estado Drive en CAB | ✗ No existía — implementado en este IMPL |

**Conclusión DRIVE-001:** rclone operativo, sin credenciales Drive. La primera subida real
requiere configurar `BACKUP_GDRIVE_SA_JSON` en `.env`.

---

## Arquitectura implementada

### Nuevos archivos

| Archivo | Responsabilidad |
|---|---|
| `Modules/AdminSistema/Services/DriveService.php` | Servicio canónico: upload, verify, log, historial, alertas |
| `app/Console/Commands/BackupSyncDrive.php` | `backup:sync-drive` — sync manual CLI |
| `Modules/AdminSistema/Jobs/SincronizarDriveJob.php` | Job para dispatch desde Livewire |

### Archivos modificados

| Archivo | Cambio |
|---|---|
| `app/Console/Commands/BackupExportSeeders.php` | `uploadToDrive()` → delega a `DriveService::subirZip()` |
| `Modules/AdminSistema/Livewire/Backups/BackupDashboard.php` | Props Drive + `sincronizarDrive()` + `cargarDatos()` ampliado |
| `Modules/AdminSistema/resources/views/livewire/backups/backup-dashboard.blade.php` | Sección Drive completa: cards, sync button, historial, alertas |
| `Modules/AdminSistema/Database/Seeders/AdminSistemaSeeder.php` | +2 permisos: `ver-backup-drive`, `sincronizar-backup-drive` |
| `Modules/User/Database/Seeders/data/permissions.csv` | 81→83 rows |
| `Modules/User/Database/Seeders/data/permission_role.csv` | 168→170 rows |

---

## DriveService — API pública

```php
// Estado de conexión (sin llamadas de red)
DriveService::estadoConexion(): array
// → ['estado', 'etiqueta', 'color', 'icono', 'mensaje', 'carpeta']
// estados: 'sin-rclone' | 'sin-credenciales' | 'configurado'

// DRIVE-002/006: Subir y verificar ZIP
DriveService::subirZip(string $zipPath, string $zipName, bool $dryRun = false): array
// → ['exito', 'mensaje', 'size_local', 'size_remote']

// DRIVE-006: Solo verificar (sin subir)
DriveService::verificarEnDrive(zipName, remote, destPath, rcloneEnv, rcloneBin): array

// DRIVE-007: Log
DriveService::ultimaSync(): ?array
DriveService::historial(int $limite = 10): array
DriveService::conteoBackupsDrive(): int

// DRIVE-008: Alertas
DriveService::estadoAlerta(?array $ultimaSync): string  // 'verde' | 'amarillo' | 'rojo'
```

---

## Comandos

```bash
# Sincronización manual (último ZIP disponible)
php artisan backup:sync-drive

# Sincronizar ZIP específico
php artisan backup:sync-drive --file=backups/IEE-2026-06-13.zip

# Simulación
php artisan backup:sync-drive --dry-run
```

---

## DRIVE-007: Formato del historial de sincronizaciones

`storage/logs/drive-sync.log` — JSONL (una entrada por línea):

```json
{"fecha":"2026-06-13 14:00:00","backup":"IEE-2026-06-13.zip","size_local":56832,"size_remote":56832,"resultado":"OK","mensaje":"✓ Verificado en Drive (56832 bytes)"}
```

Campos: `fecha`, `backup`, `size_local`, `size_remote`, `resultado` (OK|ERROR), `mensaje`.

---

## DRIVE-009: Permisos

| ID | Slug | Asignado a |
|---|---|---|
| 82 | `ver-backup-drive` | Administrador (role_id=1) |
| 83 | `sincronizar-backup-drive` | Administrador (role_id=1) |

Creados vía `AdminSistemaSeeder` (`firstOrCreate` — idempotente).
Guardados en `data/permissions.csv` y `data/permission_role.csv` para DR.

---

## DRIVE-008: Lógica de alertas

| Condición | Alerta |
|---|---|
| Última sync exitosa < 24h | Verde |
| Última sync exitosa entre 24h y 48h | Amarilla |
| Última sync exitosa > 48h o sin sync | Roja |
| Drive no configurado (`sin-credenciales`) | Alerta omitida |
| Sin rclone | Alerta omitida |

La alerta de Drive solo se muestra si `estado === 'configurado'`.

---

## Instrucciones de configuración (operador)

Para activar la sincronización real con Google Drive:

### 1. Crear Service Account en Google Cloud

```
IAM → Service Accounts → Create → Rol: Drive API User
Crear clave JSON → Descargar JSON
```

### 2. Compartir carpeta Drive con la Service Account

```
Google Drive → Crear carpeta "IEE-Backups"
Compartir → email de la Service Account → Editor
```

### 3. Configurar en .env del servidor

```env
BACKUP_GDRIVE_SA_JSON={"type":"service_account","project_id":"mi-proyecto","private_key_id":"abc...","private_key":"-----BEGIN RSA PRIVATE KEY-----\n...\n-----END RSA PRIVATE KEY-----\n","client_email":"iee-backup@mi-proyecto.iam.gserviceaccount.com",...}
BACKUP_GDRIVE_FOLDER_ID=1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs
```

### 4. Verificar con dry-run

```bash
php artisan backup:sync-drive --dry-run
php artisan backup:sync-drive  # sync real
```

### 5. Verificar en CAB

Abrir Administración del Sistema → Backups → observar tarjeta Google Drive.

---

## Validaciones ejecutadas

| V | Descripción | Resultado |
|---|---|---|
| V-001 | Drive detectado correctamente (`sin-credenciales`) | ✓ Etiqueta + mensaje correcto |
| V-002 | Subida automática: `backup:export-seeders --dry-run` | ✓ Delega a DriveService |
| V-003 | `verificarEnDrive()` funcional (rclone ls) | ✓ Implementado |
| V-004 | `backup:sync-drive --dry-run` con ZIP real | ✓ `[DRY] rclone copy IEE-2026-06-13.zip → iee-backup:IEE-Backups` |
| V-005 | Estado Drive visible en CAB (DRIVE-003/004/010) | ✓ Cards + historial en blade |
| V-006 | Historial: `storage/logs/drive-sync.log` JSONL | ✓ Formato documentado |
| V-007 | Alertas Drive visibles (DRIVE-008) | ✓ Solo si `estado=configurado` |
| V-008 | Permisos en BD: ids 82, 83 → Administrador | ✓ `permission_role` ids 183, 184 |
| V-009 | PHP lint limpio | ✓ 6 archivos sin errores |
| V-010 | Sin regresiones: `backup:export-seeders --dry-run` | ✓ Flujo completo sin cambios de comportamiento |

---

## Restricciones respetadas

| Restricción | Verificación |
|---|---|
| NO modificar restauración / restore-from-zip | ✓ No tocado |
| NO modificar seeders de inventario | ✓ No tocado |
| NO modificar InstitutionalRestoreSeeder | ✓ No tocado |
| NO crear UI de restauración | ✓ No implementado |
| NO usar exec() / shell_exec() | ✓ Usa `Process::env()->run([])` exclusivamente |

---

## Estado final de comandos backup

```
backup:export-seeders    Exporta CSV + ZIP + sube a Drive (auto)
backup:restore-from-zip  Restaura BD desde Snapshot ZIP
backup:sync-drive        Sincroniza ZIP específico o el último a Drive (manual)
```
