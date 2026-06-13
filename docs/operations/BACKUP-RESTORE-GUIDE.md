# Guía de Respaldo y Restauración — IEE

**Sistema:** BhagamApps Modular — IEE  
**Versión guía:** 1.0 (2026-06-13)  
**Implementación:** IMPL-INFRA-BACKUP-001

---

## 1. Descripción del Sistema

IEE usa una estrategia de respaldo en dos capas:

| Capa | Qué respalda | Herramienta |
|---|---|---|
| **Código** | Todo el código fuente, configuraciones, migraciones | GitHub |
| **Datos** | Registros de BD (CSV comprimidos en ZIP) | Google Drive |

Los respaldos de datos se generan automáticamente cada noche a las 02:00
mediante el comando `php artisan backup:export-seeders`.

---

## 2. Respaldo Manual

### Ejecutar backup ahora

```bash
cd /home/adolfo/web/bhagamapps.com/private/bhagamappsModular

# Simulación primero (no escribe nada)
php artisan backup:export-seeders --dry-run

# Backup completo
php artisan backup:export-seeders
```

### Backup solo local (sin Drive)

```bash
php artisan backup:export-seeders --skip-drive
```

### Opciones disponibles

| Opción | Descripción |
|---|---|
| `--dry-run` | Simula sin crear archivos |
| `--skip-drive` | No sube a Google Drive |
| `--date=YYYY-MM-DD` | Usar una fecha específica para el nombre del directorio |

---

## 3. Verificar el Último Respaldo

```bash
# Listar backups disponibles
ls -lh backups/*.zip

# Ver metadata del último respaldo
cat backups/$(ls backups/*.zip | sort | tail -1 | xargs basename .zip | sed 's/IEE-//').json 2>/dev/null \
  || cat backups/$(ls backups/ -t | head -2 | tail -1)/metadata.json

# Verificar integridad del ZIP
unzip -t backups/IEE-$(date +%Y-%m-%d).zip

# Ver log del scheduler
tail -50 storage/logs/backup.log
```

---

## 4. Configuración de Google Drive

### Prerequisitos

- rclone instalado (`/usr/bin/rclone` — ya disponible en este servidor)
- Cuenta de Google Workspace o Google personal
- Carpeta creada en Google Drive para los respaldos

### 4.1 Modo Service Account (recomendado para servidor)

**¿Por qué Service Account?** No requiere interacción del usuario, no expira,
es el estándar para servidores y automatizaciones.

#### Paso 1 — Crear Service Account en Google Cloud

```
1. Ir a https://console.cloud.google.com
2. Crear proyecto "IEE-Backups" (o usar existente)
3. APIs → Habilitar "Google Drive API"
4. IAM → Service Accounts → Crear
   - Nombre: iee-backup-sa
   - Rol: ninguno (se asigna a carpeta Drive)
5. Generar clave JSON → Descargar
```

#### Paso 2 — Compartir carpeta en Google Drive

```
1. Crear carpeta "IEE-Backups" en Google Drive
2. Click derecho → Compartir
3. Añadir el email del Service Account: iee-backup-sa@proyecto.iam.gserviceaccount.com
4. Rol: Editor
5. Copiar el ID de la carpeta de la URL:
   https://drive.google.com/drive/folders/{FOLDER_ID}
```

#### Paso 3 — Configurar variables de entorno

Editar `.env` en el servidor:

```ini
BACKUP_RCLONE_REMOTE=iee-backup
BACKUP_RCLONE_DEST=IEE-Backups
BACKUP_RCLONE_BIN=/usr/bin/rclone

# Pegar el contenido del JSON en una sola línea (sin saltos de línea)
BACKUP_GDRIVE_SA_JSON={"type":"service_account","project_id":"...","private_key":"-----BEGIN RSA PRIVATE KEY-----\n...\n-----END RSA PRIVATE KEY-----\n","client_email":"..."}

# ID de la carpeta de Google Drive
BACKUP_GDRIVE_FOLDER_ID=1aBcD2eFgHiJkLmNoPqRsTuV
```

> **Nota:** El JSON del Service Account debe estar en una sola línea en el `.env`.
> Para convertirlo: `jq -c . < sa-credentials.json` y luego copiar el resultado.

#### Paso 4 — Probar

```bash
php artisan backup:export-seeders --dry-run
# Verificar que muestra: [DRY] rclone copy IEE-YYYY-MM-DD.zip → iee-backup:IEE-Backups

php artisan backup:export-seeders
# Verificar que muestra: ✓ Subido a Drive: iee-backup:IEE-Backups/IEE-YYYY-MM-DD.zip
```

### 4.2 Modo Remote Pre-configurado (alternativa)

Si se prefiere configurar rclone directamente (con OAuth, útil para pruebas):

```bash
# En una máquina con navegador:
rclone config create iee-backup drive scope drive.file

# Copiar el archivo de configuración al servidor:
scp ~/.config/rclone/rclone.conf usuario@servidor:~/.config/rclone/rclone.conf

# En el servidor, verificar:
rclone ls iee-backup:IEE-Backups
```

---

## 5. Política de Retención

El sistema aplica automáticamente:

| Tipo | Regla |
|---|---|
| **Diario** | Los 30 ZIPs más recientes se conservan |
| **Mensual** | El primer backup de cada mes se conserva por 12 meses |
| **Limpieza** | ZIPs y directorios fuera de estas reglas se eliminan automáticamente |

Los backups en Google Drive **no** se purgan automáticamente (la retención solo
opera en local). Purgar Drive manualmente si se requiere.

---

## 6. Programación Automática (Cron)

El scheduler de Laravel está configurado en `app/Console/Kernel.php`.
Para activarlo en producción:

```bash
# Ver cron actual
crontab -l

# Editar cron
crontab -e

# Añadir la siguiente línea:
* * * * * cd /home/adolfo/web/bhagamapps.com/private/bhagamappsModular && php artisan schedule:run >> /dev/null 2>&1
```

El backup se ejecutará cada día a las 02:00.
Los logs se guardan en `storage/logs/backup.log`.

---

## 7. Procedimiento de Restauración (BACKUP-009)

> **Importante:** La restauración automatizada (Fase 2) no está implementada.
> Este procedimiento es manual. Ejecutar con extremo cuidado en producción.

### 7.1 Escenario: pérdida total del servidor

```bash
# 1. Clonar repositorio
git clone https://github.com/bhagam19/bhagamappsModular.git
cd bhagamappsModular

# 2. Instalar dependencias
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 3. Configurar entorno
cp .env.example .env
# → Editar .env con credenciales de BD, APP_KEY, etc.
php artisan key:generate

# 4. Crear tablas (fresh = sin datos)
php artisan migrate:fresh

# 5. Descargar respaldo desde Google Drive
# → Descargar IEE-YYYY-MM-DD.zip desde la carpeta IEE-Backups
unzip IEE-YYYY-MM-DD.zip -d backup-restore/

# 6. Copiar CSVs a los directorios de seeders
cp backup-restore/permissions.csv    Modules/User/Database/Seeders/data/
cp backup-restore/permission_role.csv Modules/User/Database/Seeders/data/
cp backup-restore/users.csv          Modules/User/Database/Seeders/data/
cp backup-restore/bienes.csv         Modules/Inventario/Database/Seeders/data/
cp backup-restore/detalles.csv       Modules/Inventario/Database/Seeders/data/
cp backup-restore/categorias.csv     Modules/Inventario/Database/Seeders/data/
cp backup-restore/dependencias.csv   Modules/Inventario/Database/Seeders/data/
# (repetir para todas las tablas)

# 7. Ejecutar seeders
php artisan db:seed --class=Modules\\Apps\\database\\seeders\\AppsDatabaseSeeder
php artisan db:seed --class=Modules\\User\\Database\\Seeders\\UserDatabaseSeeder
php artisan db:seed --class=Modules\\Inventario\\Database\\Seeders\\InventarioDatabaseSeeder

# 8. Storage
php artisan storage:link

# 9. Verificar
php artisan migrate:status
```

### 7.2 Limitaciones conocidas en la restauración

| Problema | Causa | Workaround |
|---|---|---|
| `categorias`, `dependencias`, `roles` tienen IDs distintos | Seeders usan auto-increment | Ejecutar el script de restore manual que inserta con `id` explícito y deshabilita FKs |
| `users.password` siempre regenerado | `UserSeeder` recalcula password | Usuarios deben cambiar contraseña tras restore; o usar `users.csv` del backup directamente |
| Imágenes de bienes no incluidas | `bienes_imagenes` solo exporta metadatos | Restaurar desde respaldo de `storage/app/` si existe |

### 7.3 Script de insert directo (cuando seeders cambian IDs)

Para tablas donde el ID es crítico, usar MySQL directamente:

```bash
DB_PASS=$(grep '^DB_PASSWORD=' .env | cut -d= -f2-)

# Deshabilitar FKs, insertar, rehabilitar
mysql -u usuario -p"$DB_PASS" nombre_db <<'SQL'
SET FOREIGN_KEY_CHECKS=0;

LOAD DATA LOCAL INFILE '/ruta/a/categorias.csv'
INTO TABLE categorias
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS
(id, nombre, created_at, updated_at);

SET FOREIGN_KEY_CHECKS=1;
SQL
```

---

## 8. Verificación post-restauración

```bash
# Conteos básicos
php artisan tinker --execute="
echo 'bienes: ' . DB::table('bienes')->count() . PHP_EOL;
echo 'users: ' . DB::table('users')->count() . PHP_EOL;
echo 'dependencias: ' . DB::table('dependencias')->count() . PHP_EOL;
"

# Acceso al sistema
# → Navegar a /inventario → Dashboard
# → Verificar bienes, categorías, dependencias
# → Iniciar sesión con usuario conocido
```

---

## 9. Contacto y escalación

Ante cualquier duda durante la restauración, consultar:

- Impl doc: `docs/impl/IMPL-INFRA-BACKUP-001-Backups-Institucionales.md`
- Metadata del respaldo: `metadata.json` dentro del ZIP
- Historial de backups: Google Drive → carpeta IEE-Backups
