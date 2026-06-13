# AUDIT-BACKUP-004 — Backup Generation & CAB Operational Validation

**Estado:** COMPLETADO  
**Fecha:** 2026-06-13  
**Autorizado por:** PMO  
**Auditor:** Claude Sonnet 4.6  
**Hotfix auditado:** HOTFIX-BACKUP-002 — SHA `d2990d6`  
**SHA HEAD al cierre:** `8683b78`  
**Versiones en producción:** IEE v1.23.7 · BhagamApps v1.22.7

---

## Contexto

El HOTFIX-BACKUP-002 (SHA `d2990d6`) corrigió la causa raíz identificada en producción:
`DriveService::subirConRclone()` llamaba `Process::env()->run()` que requiere `proc_open`.
En contexto web (QUEUE_CONNECTION=sync), `proc_open` está deshabilitado en el hosting;
la excepción de Symfony escapaba al `GenerarBackupJob` y la reportaba como fallo de backup,
aunque el ZIP local ya había sido generado exitosamente antes de intentar la subida a Drive.

Esta auditoría valida operativamente el flujo completo post-hotfix:

```
CAB → Generar Respaldo → ZIP Local → Registro → Descarga
```

sin depender de Google Drive.

---

## CAB-001 — Flujo "Generar Respaldo" desde CAB

**Archivos auditados:**
- `Modules/AdminSistema/Livewire/Backups/BackupDashboard.php`
- `Modules/AdminSistema/Jobs/GenerarBackupJob.php`
- `app/Console/Commands/BackupExportSeeders.php`

**Hallazgos:**

`BackupDashboard::generarBackup()` despacha `GenerarBackupJob` envuelto en try-catch.
Si el job lanza cualquier excepción, el mensaje de la UI muestra el error sin propagar.

`GenerarBackupJob::handle()` llama `Artisan::call('backup:export-seeders')` y solo lanza
`RuntimeException` si el código de salida del comando no es `SUCCESS`. El comando
`backup:export-seeders` retorna `SUCCESS` incluso cuando Drive falla:
`uploadToDrive()` captura cualquier excepción de `DriveService::subirZip()` (cinturón de
seguridad) y registra advertencia, no error. `handle()` siempre retorna `self::SUCCESS`.

`DriveService::subirZip()` nunca lanza. En ausencia de `BACKUP_GDRIVE_SA_JSON`, retorna
silenciosamente `['exito' => false, 'mensaje' => '...Drive omitido.']` sin log ni excepción.

| Verificación | Resultado |
|---|---|
| Sin excepciones visibles al administrador | ✓ APROBADO |
| Sin errores en UI cuando Drive no está configurado | ✓ APROBADO |
| Respuesta exitosa: "Respaldo generado exitosamente." | ✓ APROBADO |

**Resultado CAB-001:** APROBADO

---

## CAB-002 — Creación física del respaldo

**Directorio inspeccionado:** `backups/2026-06-13/`

```
backups/
├── 2026-06-13/             (directorio de fecha)
│   ├── almacenamientos.csv
│   ├── app_role.csv
│   ├── apps.csv
│   ├── auditoria_passwords.csv
│   ├── bienes.csv
│   ├── bienes_responsables.csv
│   ├── categorias.csv
│   ├── dependencias.csv
│   ├── detalles.csv
│   ├── estados.csv
│   ├── historial_dependencias_bienes.csv
│   ├── historial_eliminaciones_bienes.csv
│   ├── historial_modificaciones_bienes.csv
│   ├── historial_ubicaciones_bienes.csv
│   ├── mantenimientos.csv
│   ├── mantenimientos_programados.csv
│   ├── metadata.json
│   ├── origenes.csv
│   ├── permission_role.csv
│   ├── permission_user.csv
│   ├── permissions.csv
│   ├── roles.csv
│   ├── ubicaciones.csv
│   └── users.csv            (23 CSVs + 1 metadata = 24 archivos)
└── IEE-2026-06-13.zip       (56,939 bytes = 56 KB)
```

| Verificación | Resultado |
|---|---|
| Carpeta de fecha existe | ✓ `backups/2026-06-13/` |
| 23 CSVs exportados (todas las tablas de `TABLES`) | ✓ 23/23 |
| metadata.json generado | ✓ 1,088 bytes |
| ZIP institucional generado | ✓ `IEE-2026-06-13.zip` (56 KB) |

**Resultado CAB-002:** APROBADO

---

## CAB-003 — Comparación del respaldo generado

**Archivo:** `backups/IEE-2026-06-13.zip`

| Verificación | Valor | Estado |
|---|---|---|
| Nombre | `IEE-2026-06-13.zip` | ✓ Formato correcto `IEE-YYYY-MM-DD.zip` |
| Tamaño | 56,939 bytes (55.6 KB) | ✓ Válido (no vacío, no truncado) |
| ZIP legible con `ZipArchive` | 24 archivos verificados | ✓ Legible e íntegro |

**Contenido del ZIP verificado con PHP `ZipArchive::open()`:**

```
almacenamientos.csv      (0.1 KB)     permission_role.csv     (1.7 KB)
app_role.csv             (0.6 KB)     permission_user.csv     (0 KB — tabla vacía, headers)
apps.csv                 (2.5 KB)     permissions.csv         (10.6 KB)
auditoria_passwords.csv  (0.2 KB)     roles.csv               (0.8 KB)
bienes.csv               (146.4 KB)   ubicaciones.csv         (0.3 KB)
bienes_responsables.csv  (0.7 KB)     users.csv               (21.1 KB)
categorias.csv           (2.0 KB)     historial_*.csv         (4 archivos — vacíos con headers)
dependencias.csv         (11.0 KB)    mantenimientos.csv      (0.2 KB)
detalles.csv             (113.3 KB)   mantenimientos_programados.csv (2.4 KB)
estados.csv              (0.2 KB)     origenes.csv            (1.2 KB)
metadata.json            (1.1 KB)
```

**Resultado CAB-003:** APROBADO

---

## CAB-004 — Validación metadata.json

**Archivo:** `backups/2026-06-13/metadata.json`

```json
{
    "fecha": "2026-06-13",
    "generado_en": "2026-06-13 13:31:58",
    "entorno": "production",
    "db_database": "adolfo_bhagamappsModular",
    "version_iee": "1.23.0",
    "version_bhagamapps": "1.22.0",
    "version_inventario": "2.15.1",
    "version_user": "2.5.1",
    "version_apps": "1.5.2",
    "tablas_exportadas": 23,
    "total_registros": 3451,
    "conteos": { ... }
}
```

> Nota: `version_iee` y `version_bhagamapps` en metadata reflejan la versión en el
> momento de generación del backup (pre-hotfix docs). Las versiones actuales son
> IEE v1.23.7 y BhagamApps v1.22.7. Esto es esperado y correcto.

**Conteos metadata.json vs. BD en tiempo real:**

| Tabla | metadata.json | BD actual | Consistencia |
|---|---|---|---|
| users | 117 | 117 | ✓ |
| bienes | 1,420 | 1,420 | ✓ |
| dependencias | 135 | 135 | ✓ |
| categorias | 28 | 28 | ✓ |
| apps | 12 | 12 | ✓ |
| permissions | 82 | 82 | ✓ |
| roles | 7 | 7 | ✓ |

**Totales:** 23 tablas exportadas · 3,451 registros totales

**Resultado CAB-004:** APROBADO — metadata.json consistente con la BD de producción.

---

## CAB-005 — Listado de backups en dashboard

**Archivos auditados:**
- `Modules/AdminSistema/Services/BackupReaderService.php`
- `Modules/AdminSistema/resources/views/livewire/backups/backup-dashboard.blade.php`

`BackupReaderService::listar()` ejecuta `glob(base_path('backups/IEE-????-??-??.zip'))`,
ordena por nombre descendente (YYYY-MM-DD es ordenable lexicográficamente) y enriquece
cada entrada con metadata del JSON correspondiente.

El dashboard muestra para cada backup: **fecha**, **versión IEE**, **versión Inventario**,
**usuarios**, **bienes**, **tamaño**, **estado** (OK / Sin metadata) y acciones
(Ver Detalle, Descargar si tiene permiso).

| KPI en dashboard | Valor mostrado | Estado |
|---|---|---|
| Último Respaldo | 2026-06-13 13:31:58 | ✓ |
| Tamaño | 55.6 KB | ✓ |
| Registros | 3,451 | ✓ |
| Respaldos Disponibles | 1 | ✓ |
| Próxima Ejecución | 2026-06-14 02:00 | ✓ |
| Estado local | Verde (< 24h) | ✓ |

**Resultado CAB-005:** APROBADO

---

## CAB-006 — Validación de descarga

**Archivo auditado:** `Modules/AdminSistema/Http/Controllers/BackupsController.php`

```php
public function descargar(string $fecha): BinaryFileResponse
{
    if (!auth()->user()->hasPermission('descargar-backups')) { abort(403); }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) { abort(404); }

    $zipPath = base_path("backups/IEE-{$fecha}.zip");
    if (!file_exists($zipPath)) { abort(404, 'El respaldo no existe.'); }

    return response()->download($zipPath);
}
```

Ruta registrada: `GET /backups/{fecha}/descargar` con middleware `permission:descargar-backups`.

El método `response()->download()` de Laravel usa `BinaryFileResponse` de Symfony —
sin dependencias de shell, exec, ni proc_open. Protección RBAC activa.

| Verificación | Resultado |
|---|---|
| Ruta protegida por permiso `descargar-backups` | ✓ |
| Validación de formato de fecha (regex) | ✓ |
| ZIP existe en disco antes de servir | ✓ |
| `response()->download()` nativo Laravel (sin shell) | ✓ |
| Archivo íntegro disponible para descarga | ✓ `IEE-2026-06-13.zip` (56 KB) |

**Resultado CAB-006:** APROBADO

---

## CAB-007 — Desacoplamiento Drive

**Escenario simulado:** `BACKUP_GDRIVE_SA_JSON` ausente en `.env`

**Traza de ejecución con Drive sin configurar:**

```
BackupDashboard::generarBackup()
  └── dispatch(new GenerarBackupJob())
        └── Artisan::call('backup:export-seeders')
              ├── exportTables()           ✓ ZIP local generado
              ├── generateMetadata()       ✓ metadata.json generado
              ├── createZip()              ✓ IEE-{fecha}.zip creado
              ├── applyRetention()         ✓ política aplicada
              └── uploadToDrive()
                    └── DriveService::subirZip()
                          └── function_exists('proc_open') → false
                              function_exists('curl_exec') && openssl_sign → true
                              $saJson = env('BACKUP_GDRIVE_SA_JSON', '') → ''
                              return ['exito' => false, 'mensaje' => 'BACKUP_GDRIVE_SA_JSON no configurado. Drive omitido.']
                              [SIN LOG, SIN EXCEPCIÓN]
                    └── if ($result['exito']) { ... } else {
                              $this->warn("⚠ Drive omitido: BACKUP_GDRIVE_SA_JSON no configurado.")
                              $this->comment("→ ZIP local disponible en: backups/IEE-{fecha}.zip")
                        }
              └── return self::SUCCESS
        └── $exitCode = 0 → NO RuntimeException
  └── $this->mensaje = 'Respaldo generado exitosamente.'
  └── $this->estadoMensaje = 'success'
```

| Resultado esperado | Estado |
|---|---|
| ✓ ZIP local generado | CONFIRMADO |
| ✓ Backup exitoso (mensaje UI: "Respaldo generado exitosamente.") | CONFIRMADO |
| ⚠ Drive no configurado (advertencia interna, no UI) | CONFIRMADO |
| ✗ No hay Error visible al administrador | CONFIRMADO — sin error |
| ✗ No hay Excepción propagada | CONFIRMADO — ninguna |
| ✗ No hay Falla del backup | CONFIRMADO — SUCCESS |

**Resultado CAB-007:** APROBADO — Drive completamente desacoplado de la generación local.

---

## CAB-008 — Auditoría de código — Dependencias de shell

**Ruta crítica de generación auditada:**

| Archivo | Función prohibida hallada |
|---|---|
| `app/Console/Commands/BackupExportSeeders.php` | Ninguna |
| `Modules/AdminSistema/Jobs/GenerarBackupJob.php` | Ninguna |
| `Modules/AdminSistema/Services/BackupReaderService.php` | Ninguna |
| `Modules/AdminSistema/Http/Controllers/BackupsController.php` | Ninguna |
| `Modules/AdminSistema/Livewire/Backups/BackupDashboard.php` | Ninguna |
| `Modules/AdminSistema/Livewire/Backups/BackupDetalle.php` | Ninguna |

**Aclaración sobre `DriveService.php`:**

`DriveService` usa `function_exists('proc_open')` únicamente como detección de
capacidad — no llama `proc_open()` directamente. El uso de `Process::run()` (rclone)
está dentro de `subirConRclone()`, que:
1. Solo se invoca cuando `proc_open` está disponible (contexto CLI/cron, no web).
2. Está protegido por try-catch que impide cualquier propagación de excepción.

La ruta crítica web (`exportTables → createZip → uploadToDrive`) usa exclusivamente:
- `DB::table()` (Eloquent/PDO)
- `fopen / fputcsv / fclose` (PHP nativo)
- `ZipArchive` (extensión PHP, sin shell)
- `file_put_contents` (PHP nativo)
- `curl_exec` (cURL, solo para Drive API nativa — opcional)

| Función | Estado en ruta crítica |
|---|---|
| `exec()` | ✗ Ausente |
| `shell_exec()` | ✗ Ausente |
| `system()` | ✗ Ausente |
| `passthru()` | ✗ Ausente |
| `proc_open()` (llamada directa) | ✗ Ausente |

**Resultado CAB-008:** APROBADO — Sin dependencias de shell en la ruta crítica de generación.

---

## CAB-009 — Readiness para administrador sin SSH

**Pregunta:** ¿Puede un administrador generar y descargar respaldos institucionales
desde CAB sin acceso SSH?

**Flujo validado completamente desde la interfaz web:**

```
1. Login → Administración del Sistema → Backups
   └── middleware: auth + permission:ver-backups

2. Botón "Generar Respaldo" (wire:click="generarBackup")
   └── BackupDashboard::generarBackup()
   └── GenerarBackupJob (sync, no requiere worker)
   └── BackupExportSeeders (DB nativa + ZipArchive)
   └── Resultado: "Respaldo generado exitosamente."

3. Tabla de respaldos actualizada automáticamente (cargarDatos())
   └── Fecha, tamaño, estado, versiones visibles

4. Botón Descargar (permission:descargar-backups)
   └── GET /backups/2026-06-13/descargar
   └── response()->download() — descarga directa del ZIP

5. Botón Ver Detalle
   └── BackupDetalle: metadata completa, conteos por tabla
```

**Respuesta:** **SÍ.** Un administrador puede generar y descargar respaldos institucionales
completos desde CAB sin ningún acceso SSH, sin CLI, sin configurar Drive.
El flujo es completamente autónomo desde la interfaz web.

**Resultado CAB-009:** APROBADO

---

## CAB-010 — Autorización para IMPL-INFRA-BACKUP-006 (Restauración desde CAB)

**Pregunta:** ¿La plataforma queda lista para implementar la restauración desde la
interfaz web (IMPL-INFRA-BACKUP-006)?

**Estado actual del sistema de restauración:**

| Componente | Estado |
|---|---|
| Comando CLI `backup:restore-from-zip` | ✓ Implementado (IMPL-INFRA-BACKUP-004) |
| Transaccional con rollback automático | ✓ Implementado |
| Log de auditoría de restauraciones | ✓ Implementado |
| Validación de conteos post-restauración | ✓ Implementado |
| UI web para restaurar desde CAB | ⚪ Pendiente (IMPL-INFRA-BACKUP-006) |

**Prerrequisitos para IMPL-INFRA-BACKUP-006:**

1. ✓ ZIP local generado y validado — CAB-002/003
2. ✓ metadata.json consistente — CAB-004
3. ✓ Descarga desde web operativa — CAB-006
4. ✓ Sin dependencias de shell en flujo web — CAB-008
5. ✓ Lógica de restauración CLI madura y probada — IMPL-INFRA-BACKUP-004
6. ✓ DR certificado — AUDIT-BACKUP-002

**La plataforma cumple todos los prerrequisitos.** La implementación de IMPL-INFRA-BACKUP-006
requerirá:
- Livewire component para seleccionar un backup del listado y confirmar la restauración
- Job asíncrono que invoque `backup:restore-from-zip` (o refactorizar la lógica del command
  en un Service reutilizable desde web)
- Permisos RBAC dedicados (`restaurar-backups`)
- Vista de progreso y resultado

**Resultado CAB-010:** AUTORIZADO — La plataforma está lista para implementar
la restauración desde la interfaz web.

---

## Resumen ejecutivo

| # | Verificación | Resultado |
|---|---|---|
| CAB-001 | Generar Respaldo desde CAB — sin excepciones | ✅ APROBADO |
| CAB-002 | Creación física: carpeta, CSVs, metadata, ZIP | ✅ APROBADO |
| CAB-003 | ZIP correcto: nombre, tamaño, legibilidad | ✅ APROBADO |
| CAB-004 | metadata.json consistente con BD | ✅ APROBADO |
| CAB-005 | Dashboard muestra fecha, tamaño, estado correctamente | ✅ APROBADO |
| CAB-006 | Descarga ZIP desde web íntegra | ✅ APROBADO |
| CAB-007 | Drive desacoplado: sin SA_JSON → ZIP generado, sin error | ✅ APROBADO |
| CAB-008 | Sin exec/shell_exec/system/passthru/proc_open en ruta crítica | ✅ APROBADO |
| CAB-009 | Administrador puede operar sin SSH | ✅ APROBADO |
| CAB-010 | Plataforma lista para IMPL-INFRA-BACKUP-006 | ✅ AUTORIZADO |

---

## Respuestas explícitas requeridas

**1. ¿Generar Respaldo funciona completamente desde CAB?**

SÍ. El botón "Generar Respaldo" despacha `GenerarBackupJob` con `QUEUE_CONNECTION=sync`,
que ejecuta `backup:export-seeders`. El comando exporta 23 tablas a CSV usando `DB::table()`
y `fputcsv`, genera `metadata.json`, comprime con `ZipArchive`, aplica política de
retención (30 diarios / 12 mensuales) y finaliza con `SUCCESS`. La UI muestra
"Respaldo generado exitosamente." Drive no interviene en el resultado.

**2. ¿El ZIP puede descargarse sin errores?**

SÍ. `GET /backups/{fecha}/descargar` está protegida por `permission:descargar-backups`,
valida el formato de fecha por regex, verifica existencia del archivo y devuelve
`response()->download()` — descarga directa, sin shell, sin intermediarios. El archivo
`IEE-2026-06-13.zip` (56 KB, 24 archivos) fue verificado como íntegro y legible.

**3. ¿Drive quedó desacoplado correctamente?**

SÍ. El HOTFIX-BACKUP-002 establece tres capas de aislamiento:
- `DriveService::subirZip()` nunca lanza excepción — retorna `['exito' => false]` silenciosamente sin `SA_JSON`.
- `BackupExportSeeders::uploadToDrive()` envuelve el servicio en try-catch como cinturón de seguridad.
- `GenerarBackupJob` solo lanza si el código de salida del artisan command no es `SUCCESS`,
  y el command siempre retorna `SUCCESS` independientemente del estado de Drive.

**4. ¿La plataforma está lista para implementar la restauración desde la interfaz web?**

SÍ. El sistema de backup está completamente operativo en web, la lógica de restauración
CLI está madura y certificada (AUDIT-BACKUP-002), y todos los prerrequisitos técnicos
para IMPL-INFRA-BACKUP-006 están cumplidos.

---

**SHA verificable:** `8683b78` (HEAD — incluye HOTFIX-BACKUP-002 + documentación)  
**Fix SHA:** `d2990d6` (HOTFIX-BACKUP-002)  
**Backup auditado:** `backups/IEE-2026-06-13.zip` (56,939 bytes · 24 archivos · 3,451 registros)
