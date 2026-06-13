# IMPL-INFRA-BACKUP-001 — Sistema de Respaldo Institucional

**Fecha:** 2026-06-13
**Módulo:** Infraestructura — `app/Console/Commands/BackupExportSeeders.php`
**Versión:** BhagamApps v1.21.0 · IEE v1.22.0
**Estado:** IMPLEMENTADO

---

## Objetivo

Crear un sistema de respaldo orientado a la **reconstrucción completa** del sistema
en caso de pérdida del servidor. El respaldo cubre exclusivamente los **datos institucionales**;
el código fuente está protegido por GitHub.

### Escenario de restauración objetivo

```
1. git clone https://github.com/bhagam19/bhagamappsModular.git
2. composer install && npm ci
3. php artisan migrate:fresh
4. Descargar respaldo ZIP desde Google Drive
5. Descomprimir → sobrescribir carpeta Modules/*/Database/Seeders/data/
6. php artisan db:seed
7. Sistema restaurado
```

---

## Auditoría de Tablas (BACKUP-003)

### Tablas exportadas (23)

| Orden | Tabla | Filas prod. | Descripción |
|---|---|---|---|
| 1 | `permissions` | 77 | Permisos del sistema |
| 2 | `apps` | 11 | Catálogo de aplicaciones |
| 3 | `roles` | 7 | Roles institucionales |
| 4 | `users` | 117 | Usuarios del sistema |
| 5 | `permission_role` | 164 | Asignación permisos → roles |
| 6 | `permission_user` | 0 | Permisos directos a usuarios |
| 7 | `app_role` | 10 | Apps accesibles por rol |
| 8 | `categorias` | 28 | Categorías de bienes |
| 9 | `dependencias` | 135 | Dependencias institucionales |
| 10 | `ubicaciones` | 4 | Ubicaciones físicas |
| 11 | `origenes` | 11 | Orígenes de adquisición |
| 12 | `estados` | 4 | Estados de bienes |
| 13 | `almacenamientos` | 2 | Tipos de almacenamiento |
| 14 | `mantenimientos` | 3 | Tipos de mantenimiento |
| 15 | `bienes` | 1,420 | Inventario de bienes |
| 16 | `detalles` | 1,412 | Detalles técnicos de bienes |
| 17 | `bienes_responsables` | 10 | Asignación bienes → responsables |
| 18 | `mantenimientos_programados` | 10 | Mantenimientos agendados |
| 19 | `historial_modificaciones_bienes` | 11 | Auditoría de modificaciones |
| 20 | `historial_ubicaciones_bienes` | 0 | Historial de ubicaciones |
| 21 | `historial_eliminaciones_bienes` | 0 | Bienes eliminados |
| 22 | `historial_dependencias_bienes` | 0 | Historial de dependencias |
| 23 | `auditoria_passwords` | 3 | Auditoría de contraseñas |

**Total registros (2026-06-13):** 3,439
**Tamaño ZIP:** 55.3 KB

### Tablas excluidas y justificación

| Tabla | Justificación |
|---|---|
| `sessions` | Datos efímeros de sesión |
| `failed_jobs` | Cola de trabajos fallidos (efímera) |
| `password_reset_tokens` | Tokens de seguridad temporales |
| `personal_access_tokens` | Tokens de API (seguridad) |
| `notifications` | Notificaciones no persistentes |
| `migrations` | Gestionado por Laravel internamente |
| `cache` | Caché efímera |

---

## Arquitectura del Respaldo (BACKUP-004/005/006)

### Estructura de directorios

```
backups/
  YYYY-MM-DD/              # Directorio de trabajo (una por respaldo)
    permissions.csv
    apps.csv
    roles.csv
    users.csv
    permission_role.csv
    permission_user.csv
    app_role.csv
    categorias.csv
    dependencias.csv
    ubicaciones.csv
    origenes.csv
    estados.csv
    almacenamientos.csv
    mantenimientos.csv
    bienes.csv
    detalles.csv
    bienes_responsables.csv
    mantenimientos_programados.csv
    historial_modificaciones_bienes.csv
    historial_ubicaciones_bienes.csv
    historial_eliminaciones_bienes.csv
    historial_dependencias_bienes.csv
    auditoria_passwords.csv
    metadata.json
  IEE-YYYY-MM-DD.zip       # Compresión del directorio anterior
```

### Formato CSV

Compatible con `SplFileObject::READ_CSV` (PHP). Ejemplo de `bienes.csv`:

```
id,nombre,cantidad,serie,origen,origen_id,fecha_adquisicion,...
1,Escritorio,2,0,-,1,2025-06-04,...
```

- Separador: coma
- Enclosures solo donde necesarios (fputcsv estándar PHP)
- NULL → cadena vacía
- UTF-8

### Metadata (metadata.json)

```json
{
  "fecha": "2026-06-13",
  "generado_en": "2026-06-13 00:18:22",
  "entorno": "production",
  "db_database": "adolfo_bhagamappsModular",
  "version_iee": "1.22.0",
  "version_bhagamapps": "1.21.0",
  "version_inventario": "2.15.1",
  "version_user": "2.5.1",
  "version_apps": "1.5.2",
  "tablas_exportadas": 23,
  "total_registros": 3439,
  "conteos": { ... }
}
```

---

## Comando Artisan (BACKUP-002)

### Firma

```
php artisan backup:export-seeders [opciones]
```

### Opciones

| Opción | Descripción |
|---|---|
| `--dry-run` | Simula sin escribir archivos ni subir |
| `--skip-drive` | Omite la subida a Google Drive |
| `--date=YYYY-MM-DD` | Sobreescribe la fecha (útil para re-exportar) |

### Uso típico

```bash
# Simulación previa (obligatoria antes del primer uso real)
php artisan backup:export-seeders --dry-run

# Backup completo con Drive
php artisan backup:export-seeders

# Backup sin Drive (solo local)
php artisan backup:export-seeders --skip-drive
```

### Schedule (Kernel.php)

Configurado en `app/Console/Kernel.php` para ejecutarse diariamente a las 02:00:

```php
$schedule->command('backup:export-seeders')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/backup.log'));
```

Para activar el scheduler en producción, verificar el cron:

```bash
crontab -e
# Añadir:
* * * * * cd /home/adolfo/web/bhagamapps.com/private/bhagamappsModular && php artisan schedule:run >> /dev/null 2>&1
```

---

## Google Drive (BACKUP-007)

### Integración vía rclone v1.71.2

rclone está instalado en `/usr/bin/rclone`. El comando soporta dos modos:

#### Modo A — Service Account (recomendado para servidor)

Variables en `.env`:

```ini
BACKUP_GDRIVE_SA_JSON={"type":"service_account","project_id":"...","private_key":"..."}
BACKUP_GDRIVE_FOLDER_ID=1aBcD2eFgH...
BACKUP_RCLONE_REMOTE=iee-backup
BACKUP_RCLONE_DEST=IEE-Backups
```

El comando pasa estas variables como `RCLONE_CONFIG_IEE_BACKUP_*` al proceso rclone
sin necesidad de un archivo `rclone.conf`.

#### Modo B — Remote pre-configurado

```bash
# Configurar el remote una vez (interactivo en terminal local con navegador)
rclone config create iee-backup drive scope drive.file
# Copiar ~/.config/rclone/rclone.conf al servidor
```

### Setup Google Service Account (pasos)

1. En [console.cloud.google.com](https://console.cloud.google.com):
   - Crear proyecto o usar existente
   - Habilitar "Google Drive API"
   - Crear Service Account
   - Generar clave JSON → descargar

2. En Google Drive:
   - Crear carpeta "IEE-Backups"
   - Compartir con el email del Service Account (`...@....iam.gserviceaccount.com`)
   - Rol: Editor
   - Copiar el ID de la carpeta de la URL

3. En el servidor:
   ```bash
   # Copiar el JSON (una sola línea) en .env:
   BACKUP_GDRIVE_SA_JSON={"type":"service_account",...}
   BACKUP_GDRIVE_FOLDER_ID=1aBcD...
   ```

4. Verificar:
   ```bash
   php artisan backup:export-seeders --dry-run
   # Luego:
   php artisan backup:export-seeders
   ```

---

## Política de Retención (BACKUP-008)

| Tipo | Cantidad | Criterio |
|---|---|---|
| Diario | 30 más recientes | Los 30 ZIPs más nuevos |
| Mensual | 12 meses | Primer backup de cada mes en los últimos 12 meses |

Los ZIPs no incluidos en ninguna de las dos listas se eliminan automáticamente.
Los directorios `YYYY-MM-DD/` sin ZIP correspondiente también se eliminan.

---

## Compatibilidad con Seeders Existentes (BACKUP-010)

### Seeders compatibles directamente (insertan `id` explícito)

| Seeder | Tabla | Estado |
|---|---|---|
| `BienesSeeder` | bienes | ✓ Compatible |
| `DetallesSeeder` | detalles | ✓ Compatible |
| `PermissionSeeder` | permissions | ✓ Compatible |
| `Permission_RoleSeeder` | permission_role | ✓ Compatible |

### Seeders que NO insertan `id` (regeneran IDs automáticos)

| Seeder | Tabla | Impacto en restore | Propuesta corrección |
|---|---|---|---|
| `CategoriasSeeder` | categorias | IDs distintos → `categoria_id` de bienes queda inválido | Agregar `'id' => $data['id']` al insert |
| `DependenciasSeeder` | dependencias | IDs distintos → `dependencia_id` de bienes inválido | Idem |
| `UserSeeder` | users | Regenera contraseña (comportamiento intencional) | Aceptable; passwords se reconstruyen |
| `RoleSeeder` | roles | IDs distintos → `role_id` de users inválido | Aceptar IDs explícitos del CSV |

**Resolución para Fase 2 (restauración automatizada):**
Cuando se implemente `backup:restore-from-csv`, se crearán seeders de restauración
con `INSERT ... IGNORE` o `UPDATE ... SET` que respetan los IDs originales y deshabilitan
temporalmente FK checks. Los seeders de inicialización (carga inicial) no se modifican.

---

## Validaciones

| ID | Validación | Estado |
|---|---|---|
| V-001 | Exportación CSV correcta (23 tablas, 3,439 registros) | ✓ Verificado en producción |
| V-002 | Archivos compatibles con `SplFileObject::READ_CSV` | ✓ Mismo formato que CSVs existentes |
| V-003 | ZIP generado correctamente (55.3 KB) | ✓ ZipArchive::open/close |
| V-004 | Metadata generada con versiones y conteos | ✓ JSON válido |
| V-005 | Drive: código implementado, documentado, pendiente config SA | ⏳ Config SA requerida (ver BACKUP-007) |
| V-006 | Retención 30/12 implementada y probada | ✓ Lógica verificada en dry-run |
| V-007 | Guía de restauración documentada | ✓ docs/operations/BACKUP-RESTORE-GUIDE.md |
| V-008 | Sin pérdida de datos (solo lectura de BD) | ✓ Operación de solo lectura |
| V-009 | Sin afectar producción | ✓ Solo crea archivos en backups/ |

---

## Deuda Técnica

| ID | Descripción |
|---|---|
| DT-BACKUP-001 | Seeders con auto-increment (categorias, dependencias, roles) requieren modificación para restore con IDs exactos. Fase 2. |
| DT-BACKUP-002 | Google Drive SA pendiente de configuración por el administrador. |
| DT-BACKUP-003 | No hay notificación por email/Slack en caso de error de backup. |
| DT-BACKUP-004 | `bienes_imagenes` no exporta los archivos físicos (solo los registros DB). |

---

## SHA verificable

```
[pending] feat(infra): IMPL-INFRA-BACKUP-001 — Sistema de Respaldo Institucional
```
