# IMPL-INFRA-BACKUP-002 — Centro de Administración de Backups

| Campo           | Valor                                                              |
|-----------------|--------------------------------------------------------------------|
| **ID**          | IMPL-INFRA-BACKUP-002                                              |
| **Nombre**      | Centro de Administración de Backups                                |
| **Estado**      | COMPLETADO                                                         |
| **Fecha**       | 2026-06-13                                                         |
| **Versiones**   | IEE v1.23.0 — BhagamApps v1.22.0 — AdminSistema v1.0.0           |
| **Prerequisito**| IMPL-INFRA-BACKUP-001 (SHA: 8380fc9)                              |

---

## Objetivo

Crear el Centro de Administración de Backups accesible desde la interfaz web de IEE para que el Administrador Principal pueda gestionar, visualizar y descargar respaldos sin necesidad de SSH.

---

## Arquitectura

### Módulo: `Modules/AdminSistema`

Nuevo módulo nWidart siguiendo el patrón del proyecto (Inventario / User / Apps).

**ServiceProvider:** Auto-registra componentes Livewire por directorio, igual que `Inventario`.

### Autorización — Tres Capas (ADR-AUTHORIZATION-002)

| Capa   | Mecanismo                        | Valor               |
|--------|----------------------------------|---------------------|
| Capa 1 | `app_role` → `App::visiblesPara` | `admin-sistema`     |
| Capa 2 | Middleware `app.access:{slug}`   | `app.access:admin-sistema` |
| Capa 3 | Permisos `permission:{acción}`   | `ver-backups`, `generar-backups`, `descargar-backups` |

### Permisos creados (BACKUP-UI-008)

| Slug                | Rol asignado    | Categoría       |
|---------------------|-----------------|-----------------|
| `ver-backups`       | Administrador   | `admin-sistema` |
| `generar-backups`   | Administrador   | `admin-sistema` |
| `descargar-backups` | Administrador   | `admin-sistema` |

### App registrada en catálogo

| Campo       | Valor                            |
|-------------|----------------------------------|
| nombre      | Administración del Sistema       |
| slug        | admin-sistema                    |
| ruta        | /admin/backups                   |
| icono       | fas fa-server                    |
| habilitada  | true                             |
| Roles       | Administrador (único)            |

---

## Archivos creados

### Módulo: `Modules/AdminSistema/`

```
Modules/AdminSistema/
├── module.json
├── composer.json
├── Providers/
│   ├── AdminSistemaServiceProvider.php    ← Boot, views, Livewire auto-registro
│   ├── RouteServiceProvider.php
│   └── EventServiceProvider.php
├── Routes/
│   └── web.php                            ← Rutas con app.access:admin-sistema
├── Http/
│   └── Controllers/
│       └── BackupsController.php          ← index, detalle, descargar
├── Jobs/
│   └── GenerarBackupJob.php               ← ShouldQueue, Artisan::call
├── Services/
│   └── BackupReaderService.php            ← Lee ZIPs y metadata.json del FS
├── Livewire/
│   └── Backups/
│       ├── BackupDashboard.php            ← KPIs + listado + generación manual
│       └── BackupDetalle.php             ← Ficha técnica del respaldo
├── Database/
│   └── Seeders/
│       └── AdminSistemaSeeder.php         ← Permisos + App + app_role
└── resources/views/
    ├── components/
    │   └── footer.blade.php
    ├── backups/
    │   ├── index.blade.php               ← @livewire('backups.backup-dashboard')
    │   └── detalle.blade.php             ← @livewire('backups.backup-detalle')
    └── livewire/backups/
        ├── backup-dashboard.blade.php
        └── backup-detalle.blade.php
```

### Archivos modificados

| Archivo                                      | Cambio                                          |
|----------------------------------------------|-------------------------------------------------|
| `modules_statuses.json`                      | `"AdminSistema": true`                          |
| `config/versiones.php`                       | IEE 1.23.0, BhagamApps 1.22.0, AdminSistema 1.0.0 |
| `app/Providers/AuthServiceProvider.php`      | Gates para `ver-backups`, `generar-backups`, `descargar-backups` |
| `config/adminlte.php`                        | Menú "Administración del Sistema" → "Backups"   |

---

## Rutas registradas

| Método   | URI                                 | Nombre                   | Middleware               |
|----------|-------------------------------------|--------------------------|--------------------------|
| GET/HEAD | `/admin/backups`                    | `admin.backups.index`    | `permission:ver-backups` |
| GET/HEAD | `/admin/backups/{fecha}`            | `admin.backups.detalle`  | `permission:ver-backups` |
| GET/HEAD | `/admin/backups/{fecha}/descargar`  | `admin.backups.descargar`| `permission:descargar-backups` |

---

## Lógica de negocio

### BackupReaderService

Lee el directorio `backups/` del filesystem (no BD):
- `listar()`: Enumera `IEE-????-??-??.zip`, ordena por fecha desc, lee `metadata.json` de cada directorio.
- `leerMetadata($fecha)`: Lee `backups/{fecha}/metadata.json` → array.
- `ultimoBackup()`: Retorna el primer elemento de `listar()`.
- `estadoAlerta($ultimo)`: verde (<24h) / amarillo (24-48h) / rojo (>48h o sin backups).
- `proximaEjecucion()`: Calcula próxima ejecución a las 02:00 AM.

### GenerarBackupJob

```php
class GenerarBackupJob implements ShouldQueue
{
    public function handle(): void
    {
        Artisan::call('backup:export-seeders');
    }
}
```

`QUEUE_CONNECTION=sync` → corre síncronamente en el request actual.
Cuando se migre a queue async (database/redis), el código no necesita cambios.

### Dashboard Livewire (BACKUP-UI-003/004/007/010)

- KPI Cards: último backup, tamaño, cantidad disponible, próxima ejecución.
- Alerta coloreada por antigüedad del último backup.
- Botón "Generar Respaldo": dispatch Job + wire:loading spinner.
- Tabla listado: fecha, versión IEE, versión Inventario, usuarios, bienes, tamaño, estado.
- Acciones: ver detalle (→ ruta detalle), descargar ZIP (@can descargar-backups).

---

## Menú AdminLTE

```
MIS MÓDULOS
└── Administración del Sistema  [can: ver-backups]
     └── Backups                [route: admin.backups.index]
```

---

## Validaciones ejecutadas

| ID    | Validación                                      | Estado |
|-------|-------------------------------------------------|--------|
| V-001 | App visible únicamente para Administrador       | ✅     |
| V-002 | Dashboard carga (config:cache OK)               | ✅     |
| V-003 | Tabla de respaldos (BackupReaderService::listar)| ✅     |
| V-004 | Metadata interpretada (JSON → array)            | ✅     |
| V-005 | Descarga ZIP (response()->download())           | ✅     |
| V-006 | Generación manual (Job + dispatch)              | ✅     |
| V-007 | 3 permisos en BD, asignados a Administrador     | ✅     |
| V-008 | Menú integrado en adminlte.php                  | ✅     |
| V-009 | Sin regresiones (route:list limpio)             | ✅     |
| V-010 | PHP lint limpio — 15 archivos                   | ✅     |

---

## Restricciones aplicadas

- NO se implementó restauración.
- NO se implementó `migrate:fresh`.
- NO se modificó el motor de backup (`BackupExportSeeders`).
- NO se modificó la lógica de exportación ni los CSV.
- NO se modificó Google Drive ni la política de retención.
- Eliminación de backups: NO implementada en esta fase.

---

## Notas de producción

- Con `QUEUE_CONNECTION=sync`, la generación manual bloquea el request (~segundos).
  Para operación async, cambiar a `QUEUE_CONNECTION=database` y ejecutar `php artisan queue:work`.
- Los respaldos se leen desde `backups/` en `base_path()`, nunca desde la BD.
- La descarga ZIP sirve el archivo directamente desde el servidor — archivos de ~55 KB.

---

## SHA verificable

| Campo | Valor |
|-------|-------|
| SHA   | `10bdebdc6bab0c4d0e0222d814af50d36b86a114` |
| Commit | `feat(infra): IMPL-INFRA-BACKUP-002 — Centro de Administración de Backups` |
| Fecha  | 2026-06-13 |
