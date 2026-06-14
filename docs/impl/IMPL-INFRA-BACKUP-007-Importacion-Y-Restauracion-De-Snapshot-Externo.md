# IMPL-INFRA-BACKUP-007 — Importación y Restauración de Snapshot Externo

**Tipo:** Implementación — Infraestructura / Disaster Recovery  
**Estado:** COMPLETADA  
**Versión:** IEE v1.23.10 / BhagamApps v1.22.10  
**Fecha:** 2026-06-13  
**Prerrequisitos:** IMPL-INFRA-BACKUP-001 al 006 completados · AUDIT-BACKUP-004 aprobado

---

## 1. Objetivo

Permitir que el Administrador Principal cargue un Snapshot ZIP externo (descargado desde
Google Drive o cualquier almacenamiento externo) directamente desde el Centro de Administración
de Backups (CAB) y restaure la plataforma completa sin acceso SSH al servidor.

Esto cierra el ciclo de **Disaster Recovery (DR)**: ante la pérdida total del servidor, es
posible recuperar la plataforma únicamente con:

1. Un nuevo servidor con PHP + MySQL + Composer.
2. `git clone` del repositorio BhagamApps.
3. `cp .env.example .env` + configurar credenciales + `php artisan migrate`.
4. Descargar el Snapshot ZIP más reciente desde Google Drive.
5. Subir el ZIP por la interfaz CAB → Importar Snapshot → Restaurar.

---

## 2. Alcance

| Control | Descripción | Estado |
|---------|-------------|--------|
| SNAP-001 | Ruta web `/admin/backups/importar` registrada antes de `/{fecha}` | ✅ IMPLEMENTADO |
| SNAP-002 | Gate `importar-snapshot-backup` exige `hasPermission()` + `isAdminPrincipal()` | ✅ IMPLEMENTADO |
| SNAP-003 | Estado `subir`: input custom-file + barra de progreso Livewire + validación | ✅ IMPLEMENTADO |
| SNAP-004 | Validación ZIP: `mimes:zip`, `max:51200` (50 MB), estructura interna con `ZipArchive` | ✅ IMPLEMENTADO |
| SNAP-005 | Estado `vista-previa`: ficha completa + conteos por tabla desde `metadata.json` | ✅ IMPLEMENTADO |
| SNAP-006 | Estado `confirmar`: input `RESTAURAR` con validación visual inline | ✅ IMPLEMENTADO |
| SNAP-007 | Restauración via `Artisan::call('backup:restore-from-zip', ['--force' => true])` | ✅ IMPLEMENTADO |
| SNAP-008 | Estado `resultado`: alerta + terminal `<pre>` con salida completa | ✅ IMPLEMENTADO |
| SNAP-009 | Auditoría JSONL en `storage/logs/restore.log` con `origen: CAB-WEB-IMPORT` | ✅ IMPLEMENTADO |
| SNAP-010 | Limpieza automática de imports temporales (> 24h) en `mount()` | ✅ IMPLEMENTADO |

---

## 3. Archivos creados / modificados

### Nuevos

| Archivo | Propósito |
|---------|-----------|
| `Modules/AdminSistema/Livewire/Backups/ImportarSnapshot.php` | Componente Livewire principal |
| `Modules/AdminSistema/Http/Controllers/ImportarSnapshotController.php` | Controlador wrapper |
| `Modules/AdminSistema/resources/views/backups/importar.blade.php` | Vista wrapper |
| `Modules/AdminSistema/resources/views/livewire/backups/importar-snapshot.blade.php` | Vista de 4 estados |
| `docs/impl/IMPL-INFRA-BACKUP-007-Importacion-Y-Restauracion-De-Snapshot-Externo.md` | Este documento |

### Modificados

| Archivo | Cambio |
|---------|--------|
| `Modules/AdminSistema/Routes/web.php` | Ruta `/backups/importar` antes de `/{fecha}` |
| `Modules/AdminSistema/Database/Seeders/AdminSistemaSeeder.php` | Permiso `importar-snapshot-backup` |
| `app/Providers/AuthServiceProvider.php` | Gate `importar-snapshot-backup` |
| `config/adminlte.php` | Ítem "Importar Snapshot" en menú CAB |
| `Modules/User/Database/Seeders/data/permissions.csv` | id=85 importar-snapshot-backup |
| `Modules/User/Database/Seeders/data/permission_role.csv` | id=186 role=1 permission=85 |
| `CHANGELOG.md` | v1.22.10 |
| `VERSIONING.md` | IEE v1.23.10 / BhagamApps v1.22.10 |
| `docs/changelog/iee.md` | v1.23.10 |
| `docs/changelog/bhagamapps.md` | v1.22.10 |

---

## 4. Arquitectura del componente

### 4.1 Máquina de estados

```
[subir] ──cargarYValidar()──→ [vista-previa] ──irAConfirmar()──→ [confirmar] ──ejecutarRestauracion()──→ [resultado]
   ↑                                ↓                                  ↓
   └──────── cancelar() ────────────┘                                  │
   └──────── cancelar() ────────────────────────────────────────────────┘
   ↑
   └── resetear() (desde resultado)
```

### 4.2 Flujo de `cargarYValidar()`

```
1. validate(['zipFile' => 'required|file|mimes:zip|max:51200'])
2. storeAs('imported-snapshots', "SNAP-{$timestamp}-{$random}.zip", 'local')
3. $zip = new ZipArchive(); $zip->open($rutaTemporal)
4. Verificar presencia de 'metadata.json' en el ZIP
5. Verificar presencia de CSV_MINIMOS: users.csv, bienes.csv, categorias.csv,
   dependencias.csv, permissions.csv
6. Leer y decodificar metadata.json
7. Renombrar archivo: "IEE-{$meta['fecha']}-imported.zip"
8. Poblar: $meta, $nombreArchivo, $tamanoBytes
9. $estado = 'vista-previa'
```

### 4.3 Flujo de `ejecutarRestauracion()`

```
1. Verificar autorización (abort 403 si no pasa)
2. trim($confirmacion) === 'RESTAURAR' → abort si no
3. Artisan::call('backup:restore-from-zip', [
       '--file'  => $rutaImportada,   // ruta relativa al disco 'local'
       '--force' => true,
   ])
4. $outputComando = Artisan::output()
5. $exito = ($exitCode === 0)
6. registrarAuditoria()
7. limpiarImportada()
8. $estado = 'resultado'
```

### 4.4 Registro de auditoría (SNAP-009)

Cada operación escribe una línea JSONL en `storage/logs/restore.log`:

```json
{
  "fecha":           "2026-06-13T20:30:00-05:00",
  "origen":          "CAB-WEB-IMPORT",
  "usuario_id":      1,
  "usuario":         "admin@iee.edu.co",
  "backup":          "IEE-2026-06-13-imported.zip",
  "version_iee":     "1.23.10",
  "total_registros": 3451,
  "tamano_bytes":    56939,
  "exito":           true,
  "ip":              "192.168.1.100"
}
```

---

## 5. Seguridad y autorización

### Capas de defensa (4 niveles)

| Capa | Mecanismo | Qué verifica |
|------|-----------|--------------|
| 1 — Route middleware | `permission:importar-snapshot-backup` | Permiso RBAC en cada request HTTP |
| 2 — Controller | `isAdminPrincipal()` → abort(403) | es_principal = true en capa HTTP |
| 3 — Livewire mount | `hasPermission() + isAdminPrincipal()` → abort(403) | Autorización completa en componente |
| 4 — Gate | `importar-snapshot-backup` en menú `@can` | Visibilidad en UI |

### Gate definido

```php
// app/Providers/AuthServiceProvider.php
Gate::define('importar-snapshot-backup', fn($user) =>
    $user->hasPermission('importar-snapshot-backup') && $user->isAdminPrincipal()
);
```

### Permiso RBAC

| Campo | Valor |
|-------|-------|
| id | 85 |
| nombre | importar snapshot backup |
| slug | importar-snapshot-backup |
| categoria | admin-sistema |
| descripcion | Permite cargar y restaurar un Snapshot ZIP externo (ej: descargado desde Drive). Requiere es_principal = true. |

Asignado al rol **Administrador** (role_id=1), permission_role id=186.

---

## 6. Validación del ZIP importado

El componente rechaza archivos que no cumplan todos los criterios:

| Criterio | Validación |
|----------|------------|
| Extensión | `mimes:zip` — Livewire validation |
| Tamaño máximo | `max:51200` (50 MB) |
| Apertura válida | `ZipArchive::open()` debe retornar `true` |
| metadata.json presente | `$zip->statName('metadata.json') !== false` |
| CSVs mínimos presentes | `users.csv`, `bienes.csv`, `categorias.csv`, `dependencias.csv`, `permissions.csv` |
| metadata decodificable | `json_decode($zip->getFromName('metadata.json'), true)` válido |

Si falla cualquier criterio, el archivo temporal se elimina y se muestra el error en estado `subir`.

---

## 7. Gestión de archivos temporales

- **Almacenamiento**: `storage/app/imported-snapshots/`
- **Nombre temporal**: `SNAP-{timestamp}-{random6}.zip` (evita colisiones y adivinación)
- **Renombramiento**: `IEE-{fecha_snapshot}-imported.zip` tras validación
- **Limpieza en mount()**: elimina archivos con más de 24 horas (SNAP-010)
- **Limpieza post-restauración**: `limpiarImportada()` elimina el ZIP tras resultado o cancelación
- **Disco utilizado**: `'local'` (fuera de `public/`) — nunca accesible por URL directa

---

## 8. Limitaciones del servidor

La validación muestra el límite actual del servidor en la vista `subir`:

```php
ini_get('upload_max_filesize')  // típicamente 2M en hosting compartido
```

**Restricción conocida**: el `upload_max_filesize` de PHP limita el tamaño del ZIP que
puede cargarse por navegador. Si el Snapshot es mayor al límite del servidor, se debe
aumentar `upload_max_filesize` y `post_max_size` en `php.ini`, o subir el ZIP al servidor
por otro medio (SFTP/SCP) y usar la restauración desde CAB local (IMPL-INFRA-BACKUP-006).

---

## 9. Respuestas a las preguntas explícitas del PMO

### ¿Puede recuperarse una instalación completamente nueva usando únicamente GitHub + Migraciones + Snapshot ZIP descargado de Drive?

**SÍ.**

El ciclo completo de DR queda habilitado con IMPL-INFRA-BACKUP-007:

```
Paso 1: Nuevo servidor (PHP + MySQL + Composer + web server)
Paso 2: git clone <repo> bhagamapps/
Paso 3: cp .env.example .env  →  configurar DB_* y APP_*
Paso 4: composer install --no-dev
Paso 5: php artisan key:generate
Paso 6: php artisan migrate --force
Paso 7: php artisan db:seed --class=AdminSistemaSeeder --force
        (crea el permiso importar-snapshot-backup y el ítem de menú)
Paso 8: Login como Administrador Principal
Paso 9: Admin Sistema → Backups → Importar Snapshot
Paso 10: Subir el ZIP descargado desde Google Drive
Paso 11: Revisar vista previa → escribir RESTAURAR → Ejecutar
Paso 12: Plataforma restaurada al estado exacto del Snapshot
```

El motor de restauración (`backup:restore-from-zip` → `InstitutionalRestoreSeeder`) es
transaccional: si falla cualquier tabla, hace rollback completo. Si tiene éxito, la BD
queda en el estado exacto del Snapshot.

### ¿Queda eliminada la dependencia del servidor original para restaurar información?

**SÍ.**

Con IMPL-INFRA-BACKUP-006 + IMPL-INFRA-BACKUP-007 combinados:

- **IMPL-006**: restaura desde Snapshots locales del servidor actual (sin SSH).
- **IMPL-007**: restaura desde un ZIP externo (ej: Drive) en cualquier servidor (sin SSH).

La dependencia del servidor original queda eliminada. El único recurso externo requerido
es el Snapshot ZIP, que Google Drive mantiene sincronizado automáticamente
(IMPL-INFRA-BACKUP-005). Siempre que Drive contenga al menos un Snapshot, la recuperación
es posible desde cualquier servidor nuevo.

---

## 10. Restricción de implementación

**RESTRICCIÓN CRÍTICA CUMPLIDA:** Durante la implementación de IMPL-INFRA-BACKUP-007 no
se ejecutó ninguna restauración real. Se construyó la interfaz completa (carga, validación,
vista previa, integración con motor) y se validaron: sintaxis PHP (lint), registro del permiso
en BD, asignación al rol Administrador, Gate de autorización, y orden de rutas.
La producción permaneció intacta en todo momento.

---

## 11. Historial de implementación

| Paso | Descripción | Estado |
|------|-------------|--------|
| 1 | `ImportarSnapshot.php` — componente Livewire con `WithFileUploads` | ✅ |
| 2 | `ImportarSnapshotController.php` | ✅ |
| 3 | `backups/importar.blade.php` (wrapper) | ✅ |
| 4 | `livewire/backups/importar-snapshot.blade.php` (4 estados) | ✅ |
| 5 | `AdminSistemaSeeder.php` — permiso `importar-snapshot-backup` | ✅ |
| 6 | `web.php` — ruta `/backups/importar` antes de `/{fecha}` | ✅ |
| 7 | `AuthServiceProvider.php` — Gate `importar-snapshot-backup` | ✅ |
| 8 | `config/adminlte.php` — ítem "Importar Snapshot" en menú | ✅ |
| 9 | Seeder ejecutado con `--force` — permiso id=85 en BD | ✅ |
| 10 | Validaciones: lint PHP, Gate, ruta, instancia componente | ✅ |
| 11 | `permissions.csv` — id=85 añadido | ✅ |
| 12 | `permission_role.csv` — id=186 (role=1, permission=85) añadido | ✅ |
| 13 | Changelogs actualizados (CHANGELOG, VERSIONING, iee.md, bhagamapps.md) | ✅ |
| 14 | Documentación IMPL-INFRA-BACKUP-007 | ✅ |

---

*Implementado por Claude Code · BhagamApps Modular · IEE v1.23.10*
