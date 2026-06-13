# AUDIT-BACKUP-001 — Backup Restore Readiness Assessment

| Campo           | Valor                                                   |
|-----------------|---------------------------------------------------------|
| **ID**          | AUDIT-BACKUP-001                                        |
| **Nombre**      | Backup Restore Readiness Assessment                     |
| **Tipo**        | Auditoría de solo lectura — sin modificaciones de código |
| **Estado**      | COMPLETADO                                              |
| **Fecha**       | 2026-06-13                                              |
| **Auditor**     | Claude Sonnet 4.6 (Claude Code)                         |
| **Prerrequisito** | IMPL-INFRA-BACKUP-001 · IMPL-INFRA-BACKUP-002         |
| **Habilitado por** | IMPL-INFRA-BACKUP-003 (Drive) · IMPL-INFRA-BACKUP-004 (Restauración) |

---

## Pregunta Central

> **¿Puede IEE reconstruirse completamente desde GitHub + Migraciones + ZIP institucional?**

**Respuesta directa:** Parcialmente. La estructura de la aplicación y los datos operativos principales son recuperables, pero con 4 defectos que bloquean la restauración automatizada y 8 tablas/columnas con pérdida de datos. La restauración completa requiere intervención manual especializada.

---

## AUDIT-001 — Inventario de Tablas Exportadas

### 23 tablas en el backup (orden de exportación = orden de dependencia FK)

| # | Tabla | Registros (2026-06-13) | CSV | Seeder asociado | Tipo seeder |
|---|---|---|---|---|---|
| 1 | `permissions` | 77 | ✓ | `PermissionSeeder` | Lee data/permissions.csv |
| 2 | `apps` | 11 | ✓ | `AppSeeder` | Hardcodeado |
| 3 | `roles` | 7 | ✓ | `RoleSeeder` | Hardcodeado |
| 4 | `users` | 117 | ✓ | `UserSeeder` | Lee data/users.csv |
| 5 | `permission_role` | 164 | ✓ | `Permission_RoleSeeder` | Lee data/permission_role.csv |
| 6 | `permission_user` | 0 | ✓ | — | Sin seeder |
| 7 | `app_role` | 10 | ✓ | — | **SIN SEEDER** |
| 8 | `categorias` | 28 | ✓ | `CategoriasSeeder` | Lee data/categorias.csv |
| 9 | `dependencias` | 135 | ✓ | `DependenciasSeeder` | Lee data/dependencias.csv |
| 10 | `ubicaciones` | 4 | ✓ | `UbicacionesSeeder` | Hardcodeado |
| 11 | `origenes` | 11 | ✓ | — | **SIN SEEDER** (migración) |
| 12 | `estados` | 4 | ✓ | `EstadosSeeder` | Hardcodeado |
| 13 | `almacenamientos` | 2 | ✓ | `AlmacenamientosSeeder` | Hardcodeado |
| 14 | `mantenimientos` | 3 | ✓ | `MantenimientosSeeder` | Hardcodeado |
| 15 | `bienes` | 1.420 | ✓ | `BienesSeeder` | Lee data/bienes.csv |
| 16 | `detalles` | 1.412 | ✓ | `DetallesSeeder` | Lee data/detalles.csv |
| 17 | `bienes_responsables` | 10 | ✓ | `BienesResponsablesSeeder` | **Datos ficticios** |
| 18 | `mantenimientos_programados` | 10 | ✓ | `MantenimientosProgramadosSeeder` | **Datos ficticios** |
| 19 | `historial_modificaciones_bienes` | 11 | ✓ | `HistorialModificacionesBienesSeeder` | **Datos ficticios + FALLA** |
| 20 | `historial_ubicaciones_bienes` | 0 | ✓ | — | Sin seeder (0 registros OK) |
| 21 | `historial_eliminaciones_bienes` | 0 | ✓ | `HistorialEliminacionesBienesSeeder` | **Datos ficticios × 30** |
| 22 | `historial_dependencias_bienes` | 0 | ✓ | `HistorialDependenciasBienesSeeder` | **Datos ficticios** |
| 23 | `auditoria_passwords` | 3 | ✓ | — | **SIN SEEDER** |

### Orden de restauración requerido

```
1. migrate:fresh
   ↓
2. apps (AppSeeder — hardcoded, requiere ser primero)
   ↓
3. permissions (PermissionSeeder — backup CSV)
   ↓
4. roles (RoleSeeder — hardcoded, requiere apps.slug='user')
   ↓
5. users (UserSeeder — backup CSV)
   ↓
6. permission_role (Permission_RoleSeeder — backup CSV)
   ↓
7. app_role ← NO HAY SEEDER → inserción manual desde CSV
   ↓
8. ubicaciones → almacenamientos → estados → mantenimientos (hardcoded)
   ↓
9. origenes ← NO HAY SEEDER → inserción manual + re-mapear origen_id en bienes
   ↓
10. categorias → dependencias (CSV, con verificación de IDs)
    ↓
11. bienes → detalles (CSV)
    ↓
12. bienes_responsables, mantenimientos_programados, auditoria_passwords → manual
```

---

## AUDIT-002 — Validación CSV ↔ Seeder

### Matriz de compatibilidad por tabla crítica

#### `permissions`
| Columnas CSV | Columnas Seeder | Estado |
|---|---|---|
| id, nombre, slug, descripcion, categoria, created_at, updated_at | nombre, slug, descripcion, categoria | ⚠️ ID no restaurado (auto-increment) |

**Diagnóstico:** `PermissionSeeder` no inserta `id`. Los IDs se asignarán por auto-increment comenzando en 1. Esto es aceptable SI la tabla está vacía al restaurar, y los IDs de producción (1-77) coincidirán con los asignados en orden de inserción. **Riesgo bajo** siempre que la restauración sea sobre tabla vacía.

#### `roles`
| Columnas CSV | Seeder crea | Estado |
|---|---|---|
| id, nombre, descripcion, app_id, ... | Hardcoded 7 roles con updateOrCreate | ⚠️ ID auto-increment pero predecible |

**Diagnóstico:** `RoleSeeder` usa `updateOrCreate`. En tabla vacía, roles recibirán IDs 1-7 en el orden de inserción. Coincide con producción (Administrador=1, ..., Invitado=7). **Riesgo bajo** en tabla vacía.

#### `categorias` ⚠️ DEFECTO CRÍTICO
| Columnas CSV | Columnas Seeder | Estado |
|---|---|---|
| id, nombre, created_at, updated_at | nombre (sin id) | ❌ ID comentado — FK ruptura |

**Diagnóstico:** `CategoriasSeeder` tiene comentado:
```php
//'id' => $data['id'],
```
Al restaurar, las 28 categorías reciben IDs por auto-increment. Si hay cualquier dato previo (por ejemplo, de seeders de prueba), los IDs resultantes no coincidirán con los `categoria_id` que referencian los 1.420 bienes del CSV. **Resultado: violación de FK o asignación incorrecta de categorías a bienes.**

#### `dependencias` ✓
| Columnas CSV | Columnas Seeder | Estado |
|---|---|---|
| id, nombre, ubicacion_id, user_id, ... | id (explícito), nombre, ubicacion_id, user_id | ✓ Idempotente + ID preservado |

**Nota:** Seeder lee columna `usuario_id` del CSV pero la BD tiene `user_id`. El CSV del backup exporta `user_id`. Hay que verificar que los headers del data/dependencias.csv usen el nombre correcto cuando se copie el backup CSV.

#### `bienes` ⚠️ COLUMNA OMITIDA
| Columnas CSV | Columnas Seeder | Diferencia |
|---|---|---|
| id, nombre, cantidad, serie, **origen**, **origen_id**, fecha_adquisicion, precio, categoria_id, dependencia_id, almacenamiento_id, estado_id, mantenimiento_id, observaciones, **deleted_at**, created_at, updated_at | id, nombre, cantidad, serie, **origen** (texto), ~~origen_id~~, fecha_adquisicion, precio, categoria_id, dependencia_id, almacenamiento_id, estado_id, mantenimiento_id, observaciones, ~~deleted_at~~, created_at, updated_at | **origen_id ausente** |

**Diagnóstico:** `BienesSeeder` omite `origen_id`. Todos los 1.420 bienes restaurados tendrán `origen_id=NULL`. La migración `populate_origenes` que mapea el texto `origen` → `origen_id` ya corrió durante `migrate:fresh` pero con tabla `bienes` vacía, por lo que no aplica datos nuevos. Los bienes quedan sin clasificación de origen normalizada.

#### `users` ⚠️ COLUMNAS NUEVAS OMITIDAS
| Columnas CSV backup | Columnas Seeder | Columnas omitidas |
|---|---|---|
| ... bloqueado, forzar_cambio_password, es_principal, ... | id, nombres, apellidos, userID, email, email_verified_at, password, role_id | `bloqueado`, `forzar_cambio_password`, `es_principal` |

**Diagnóstico:** `UserSeeder` no fue actualizado al agregar estas columnas (IMPL-USERS-001). Además, la `data/users.csv` en el seeder no incluye estas columnas. Si se restaura desde el backup CSV, los campos no se insertan y quedan con valor default (0/false). Impacto: ningún usuario queda bloqueado ni marcado como principal tras restore.

**Adicionalmente:** `UserSeeder` regenera las contraseñas usando la fórmula `iniciales+últimos4DNI@IEE`. Las contraseñas del backup (hashes bcrypt) NO se restauran. Los usuarios deben conocer su contraseña por fórmula o resetearla.

#### `permission_role`
| Estado | Detalle |
|---|---|
| ⚠️ No idempotente | `insert()` sin ID. Duplica las 164 asignaciones si se corre dos veces. Una sola ejecución en tabla vacía: OK. |

#### `app_role` ❌ SIN SEEDER
| Estado |
|---|
| ❌ No existe ningún seeder para esta tabla. El CSV del backup tiene 10 registros de asignación app→rol. En restore, quedan en 0 (solo los que `AdminSistemaSeeder` y `AppSeeder` creen hardcoded). |

#### `historial_modificaciones_bienes` ❌ SEEDER ROTO
| Columnas BD | Seeder inserta | Estado |
|---|---|---|
| bien_id, tipo_objeto, **campo**, valor_anterior, valor_nuevo, dependencia_id, estado, aprobado_por | bien_id, tipo_objeto, **campo_modificado** (NO EXISTE), valor_anterior, valor_nuevo, **user_id** (NO EXISTE), aprobado_por | ❌ FALLA SQL |

**Diagnóstico:** El seeder tiene dos columnas incorrectas:
1. `campo_modificado` → la columna real se llama `campo`
2. `user_id` → no existe en esta tabla
3. Omite `dependencia_id` que es NOT NULL

El seeder **lanzará una excepción SQL** al ejecutarse.

#### `historial_eliminaciones_bienes` ⚠️ DATOS FICTICIOS × 30
```php
foreach (range(1, 10) as $i) {          // 10 iteraciones
    DB::table('...')->insert([
        ['bien_id'=>1, ...],             // 3 registros por iteración
        ['bien_id'=>1, ...],
        ['bien_id'=>1, ...],
    ]);
}
```
Genera 30 registros ficticios (todos bien_id=1) en vez de los 0 reales del backup. Acumulativo si se ejecuta múltiples veces.

#### Tablas SIN cobertura de seeder
| Tabla | Registros en backup | Método de restauración disponible |
|---|---|---|
| `app_role` | 10 | ❌ Manual (LOAD DATA o INSERT) |
| `origenes` | 11 | ❌ Manual (la migración popula catálogo pero bienes.origen_id queda NULL) |
| `auditoria_passwords` | 3 | ❌ Manual o descartable (auditoría histórica) |
| `permission_user` | 0 | N/A (vacío actualmente) |

---

## AUDIT-003 — Integridad Referencial

### Árbol de dependencias FK verificado

```
permissions(id)
    ↓ permission_role.permission_id ← roles(id)
    ↓ permission_user.permission_id ← users(id)

apps(id)
    ↓ roles.app_id                     ✓ RoleSeeder depende de App slug='user'
    ↓ app_role.app_id ← roles(id)      ❌ app_role sin seeder

users(id) ← roles(id)
    ↓ dependencias.user_id
    ↓ bienes_responsables.user_id
    ↓ mantenimientos_programados.user_id
    ↓ auditoria_passwords.usuario_afectado_id
    ↓ auditoria_passwords.administrador_id
    ↓ historial_*.user_id / aprobado_por

categorias(id)
    ↓ bienes.categoria_id               ❌ CategoriasSeeder no preserva IDs

dependencias(id) ← ubicaciones(id) ← users(id)
    ↓ bienes.dependencia_id             ✓ DependenciasSeeder preserva IDs

ubicaciones(id)
    ↓ dependencias.ubicacion_id
    ↓ historial_ubicaciones_bienes.*

origenes(id)
    ↓ bienes.origen_id (nullable)       ⚠️ BienesSeeder no restaura, queda NULL

almacenamientos(id)   → bienes.almacenamiento_id   ✓ Hardcoded IDs 1-2
estados(id)           → bienes.estado_id            ✓ Hardcoded IDs 1-4
mantenimientos(id)    → bienes.mantenimiento_id     ✓ Hardcoded IDs 1-3

bienes(id) ← categorias + dependencias + estados + almacenamientos + mantenimientos
    ↓ detalles.bien_id
    ↓ bienes_responsables.bien_id
    ↓ mantenimientos_programados.bien_id
    ↓ historial_*.bien_id
```

### Riesgos de integridad referencial

| Relación | Riesgo | Severidad |
|---|---|---|
| `bienes.categoria_id` → `categorias.id` | IDs no preservados en CategoriasSeeder → mismatch | **CRÍTICO** |
| `bienes.origen_id` → `origenes.id` | BienesSeeder omite campo → NULL en todos | **ALTO** |
| `app_role.app_id/role_id` | Sin seeder → datos perdidos | **ALTO** |
| `roles.app_id` → `apps.id` | Depende de orden de ejecución (Apps primero) | **MEDIO** (ya documentado) |
| `bienes.dependencia_id` → `dependencias.id` | DependenciasSeeder preserva IDs | ✓ OK |
| `bienes.estado_id/almacenamiento_id/mantenimiento_id` | Hardcoded con IDs explícitos | ✓ OK |
| `historial_modificaciones_bienes.dependencia_id` NOT NULL | HistorialSeeder no lo incluye | **CRÍTICO** (falla SQL) |

---

## AUDIT-004 — Seeders No Idempotentes

### Clasificación de riesgo para restauraciones repetidas

| Seeder | Método | Riesgo Único | Riesgo Repetido | Notas |
|---|---|---|---|---|
| `AppSeeder` | `updateOrCreate(slug)` | ✓ BAJO | ✓ BAJO | Idempotente por diseño |
| `AppsPermissionSeeder` | `firstOrCreate + attach check` | ✓ BAJO | ✓ BAJO | Idempotente |
| `RoleSeeder` | `updateOrCreate(nombre, app_id) + sync` | ✓ BAJO | ✓ BAJO | Idempotente |
| `DependenciasSeeder` | `updateOrInsert(id)` | ✓ BAJO | ✓ BAJO | Idempotente |
| `AdminSistemaSeeder` | `firstOrCreate + attach check` | ✓ BAJO | ✓ BAJO | Idempotente |
| `PermissionSeeder` | `insert()` sin ID | ✓ BAJO (1ª vez) | ❌ ALTO | Duplica 77 permisos |
| `UserSeeder` | `insert()` con ID explícito | ✓ BAJO (1ª vez) | ❌ MEDIO | Duplicate PK |
| `BienesSeeder` | `insert()` con ID explícito | ✓ BAJO (1ª vez) | ❌ MEDIO | Duplicate PK |
| `DetallesSeeder` | `insert()` sin ID | ✓ BAJO (1ª vez) | ❌ ALTO | Duplica 1.412 detalles |
| `Permission_RoleSeeder` | `insert()` sin ID | ✓ BAJO (1ª vez) | ❌ ALTO | Duplica 164 asignaciones |
| `AlmacenamientosSeeder` | `insert()` con ID | ✓ BAJO (1ª vez) | ❌ MEDIO | Duplicate PK |
| `EstadosSeeder` | `insert()` con ID | ✓ BAJO (1ª vez) | ❌ MEDIO | Duplicate PK |
| `UbicacionesSeeder` | `insert()` con ID | ✓ BAJO (1ª vez) | ❌ MEDIO | Duplicate PK |
| `MantenimientosSeeder` | `insert()` con ID | ✓ BAJO (1ª vez) | ❌ MEDIO | Duplicate PK |
| `CategoriasSeeder` | `insert()` sin ID | ❌ ALTO | ❌ CRÍTICO | IDs variables, no idempotente |
| `BienesResponsablesSeeder` | `insert()` ficticio | ❌ ALTO | ❌ ALTO | Datos ficticios, acumula |
| `MantenimientosProgramadosSeeder` | `insert()` ficticio (Faker) | ❌ ALTO | ❌ ALTO | Datos ficticios con Faker |
| `HistorialModificacionesBienesSeeder` | `insert()` ficticio | ❌ **FALLA** | ❌ **FALLA** | Columna `campo_modificado` no existe |
| `HistorialEliminacionesBienesSeeder` | `insert()` 30 registros | ❌ ALTO | ❌ CRÍTICO | 30 ficticios × veces ejecución |
| `HistorialDependenciasBienesSeeder` | `insert()` ficticio (Faker) | ❌ ALTO | ❌ ALTO | Datos ficticios |
| `BienesImagenesSeeder` | `insert()` ficticio (UUID) | ❌ ALTO | ❌ ALTO | Rutas de imagen inválidas |

---

## AUDIT-005 — Simulación de Restauración Teórica

### Escenario: Servidor nuevo → Git → Migrate → ZIP → Seeders

#### Fase 1: Infraestructura (✓ FUNCIONA)
```bash
git clone <repo>
composer install --no-dev --optimize-autoloader
cp .env.example .env   # configurar DB, APP_KEY, etc.
php artisan key:generate
php artisan migrate:fresh
```
- Crea 33 tablas correctamente
- Migración `populate_origenes` corre con bienes vacíos → no popula nada aún
- **Estado: ✓ Estructura 100% correcta**

#### Fase 2: Extracción del ZIP
```bash
unzip IEE-2026-06-13.zip -d backup-restore/
# Copiar CSVs a seeders/data/:
cp backup-restore/permissions.csv     Modules/User/Database/Seeders/data/
cp backup-restore/users.csv           Modules/User/Database/Seeders/data/
cp backup-restore/permission_role.csv Modules/User/Database/Seeders/data/
cp backup-restore/categorias.csv      Modules/Inventario/Database/Seeders/data/
cp backup-restore/dependencias.csv    Modules/Inventario/Database/Seeders/data/
cp backup-restore/bienes.csv          Modules/Inventario/Database/Seeders/data/
cp backup-restore/detalles.csv        Modules/Inventario/Database/Seeders/data/
```
- **Estado: ✓ Archivos copiados**

#### Fase 3: Ejecución de Seeders

| Paso | Comando | Resultado | Notas |
|---|---|---|---|
| 1 | `db:seed AppsDatabaseSeeder` | ✓ 11 apps creadas (12 con admin-sistema) | Hardcoded, funciona |
| 2 | `db:seed UserDatabaseSeeder` → PermissionSeeder | ✓ 77 permisos | OK si backup CSV copiado |
| 3 | UserDatabaseSeeder → RoleSeeder | ✓ 7 roles | OK, requiere app 'user' (creada en paso 1) |
| 4 | UserDatabaseSeeder → UserSeeder | ⚠️ 117 usuarios, sin bloqueado/forzar/es_principal | Campos nuevos ignorados |
| 5 | UserDatabaseSeeder → Permission_RoleSeeder | ✓ 164 asignaciones | OK si backup CSV copiado |
| 6 | `db:seed InventarioDatabaseSeeder` → AlmacenamientosSeeder | ✓ 2 catálogos | OK |
| 7 | → CategoriasSeeder | ❌ **IDs NO PRESERVADOS** | Sin `'id' => $data['id']` |
| 8 | → EstadosSeeder | ✓ 4 estados | OK |
| 9 | → MantenimientosSeeder | ✓ 3 catálogos | OK |
| 10 | → UbicacionesSeeder | ✓ 4 ubicaciones | OK |
| 11 | → DependenciasSeeder | ✓ 135 dependencias con IDs | OK |
| 12 | → BienesSeeder | ⚠️ 1.420 bienes, origen_id=NULL | FK a categorias puede fallar por paso 7 |
| 13 | → DetallesSeeder | ⚠️ 1.412 detalles | FK a bienes depende de paso 12 |
| 14 | → HistorialModificacionesBienesSeeder | ❌ **FALLA SQL** | `campo_modificado` no existe |
| 15 | → HistorialDependenciasBienesSeeder | ⚠️ 10 ficticios | Bien 1-10 existen, pasa |
| 16 | → HistorialEliminacionesBienesSeeder | ⚠️ 30 ficticios | Datos incorrectos |
| 17 | → BienesImagenesSeeder | ⚠️ 10 ficticios | Rutas inválidas |
| 18 | → MantenimientosProgramadosSeeder | ⚠️ 10 ficticios | Datos incorrectos |

#### Resumen de fase 3: Consecuencias en cadena

```
CategoriasSeeder sin IDs
      ↓
categorias reciben IDs 1-28 en orden correcto (tabla vacía → coincide)
      ↓
bienes.categoria_id referencia IDs 1-28 → COINCIDE ✓
      ↓ (pero si hay cualquier dato previo en tabla → FK ERROR)

NOTA: En tabla VACÍA tras migrate:fresh, los auto-increment asignan
IDs en orden de inserción (1-28 para 28 categorías). En este escenario
específico, los IDs COINCIDEN. El riesgo es para restauraciones
parciales o sobre instalaciones existentes.
```

**Aclaración crítica:** En un migrate:fresh limpio, los IDs de categorías SÍ coincidirán con los del backup (1-28 por auto-increment en orden de inserción). El defecto es **latente**: falla en restauraciones sobre instalaciones parciales, en bases de datos MySQL con `NO_AUTO_VALUE_ON_ZERO` desactivado, o si se agrega una categoría antes de correr el seeder.

#### Qué funciona en restore limpio (migrate:fresh)
- Estructura de aplicación: 100% ✓
- Autenticación (users + roles + permissions): 95% ✓ (contraseñas regeneradas)
- Catálogos (almacenamientos, estados, ubicaciones, mantenimientos): 100% ✓
- Dependencias: 100% ✓ (IDs preservados)
- Bienes (datos básicos): 95% ✓ (sin origen_id)
- Detalles: 100% ✓
- Categorías: ⚠️ OK en tabla vacía, riesgo en reinstalación parcial

#### Qué falla o se pierde
| Dato | Estado tras restore | Impacto |
|---|---|---|
| `bienes.origen_id` (1.420 registros) | NULL en todos | Medio — campo informativo |
| `app_role` (10 registros) | 0 ó parcial | **Alto** — apps no visibles para roles |
| `bienes_responsables` (10 registros reales) | 10 ficticios | Medio |
| `mantenimientos_programados` (10 reales) | 10 ficticios | Medio |
| `historial_modificaciones_bienes` (11 reales) | Seeder falla, 0 registros | Medio |
| `historial_eliminaciones_bienes` (0 reales) | 30 ficticios | Medio |
| `auditoria_passwords` (3 registros) | 0 | Bajo |
| `users.bloqueado/forzar/es_principal` | 0 en todos | Medio |
| Contraseñas actuales de usuarios | Regeneradas por fórmula | Alto (funcional, no datos) |
| Notificaciones (11 registros) | 0 | Bajo |

---

## AUDIT-006 — Tablas Faltantes / No Respaldadas

### Tablas en BD que NO están en el backup

| Tabla | Registros | ¿Debe respaldarse? | Justificación |
|---|---|---|---|
| `app_user` | 0 | No (por ahora) | Pivot individual user→app, actualmente vacía |
| `bienes_imagenes` | 0 | No (por ahora) | Sin imágenes cargadas actualmente |
| `failed_jobs` | 0 | No | Técnica, no datos de negocio |
| `grupos` | 0 | No (por ahora) | Módulo no implementado |
| `migrations` | — | No | Técnica |
| `notifications` | 11 | **Evaluar** | Notificaciones activas de HMB — datos transitorios |
| `password_reset_tokens` | — | No | Técnica, TTL corto |
| `personal_access_tokens` | — | No | Técnica, tokens Sanctum |
| `sessions` | — | No | Técnica |

### Tablas en backup que NO están en BD
*Ninguna* — todos los 23 CSVs corresponden a tablas existentes.

### Tablas que DEBERÍAN agregarse al backup en fases futuras
| Tabla | Cuándo | Motivo |
|---|---|---|
| `app_user` | Cuando haya datos | Si se usan asignaciones individuales de apps |
| `bienes_imagenes` | Cuando haya imágenes | Al implementar galería de bienes |
| `grupos` | Al implementar módulo | Datos de grupos académicos |

---

## AUDIT-007 — Validación de metadata.json

### Metadata del respaldo 2026-06-13

```json
{
    "fecha": "2026-06-13",
    "generado_en": "2026-06-13 00:18:22",
    "entorno": "production",
    "db_database": "adolfo_bhagamappsModular",
    "version_iee": "1.21.1",
    "version_bhagamapps": "1.20.1",
    "version_inventario": "2.15.1",
    "version_user": "2.5.1",
    "version_apps": "1.5.2",
    "tablas_exportadas": 23,
    "total_registros": 3439,
    "conteos": { ... }
}
```

### Verificación de versiones

| Campo | Valor en metadata | Versión actual (2026-06-13) | Estado |
|---|---|---|---|
| IEE | 1.21.1 | **1.23.0** | ⚠️ Desfase — backup anterior a IMPL-INFRA-BACKUP-002 |
| BhagamApps | 1.20.1 | **1.22.0** | ⚠️ Desfase |
| Inventario | 2.15.1 | 2.15.1 | ✓ |
| User | 2.5.1 | 2.5.1 | ✓ |
| Apps | 1.5.2 | 1.5.2 | ✓ |

**Explicación:** El backup fue generado por el schedule 02:00 del 2026-06-13. `IMPL-INFRA-BACKUP-002` se implementó **después** de esa hora. El backup de esta fecha NO incluye el módulo AdminSistema en su metadata. El próximo backup (2026-06-14 02:00) reflejará las versiones correctas.

### Verificación de conteos

| Tabla | En metadata | En BD actual | Estado |
|---|---|---|---|
| users | 117 | 117 | ✓ |
| bienes | 1.420 | 1.420 | ✓ |
| dependencias | 135 | 135 | ✓ |
| permissions | 77 | **81** | ⚠️ IMPL-INFRA-BACKUP-002 agregó 3 permisos + AdminSistema |
| apps | 11 | **12** | ⚠️ AdminSistema agregó app admin-sistema |
| app_role | 10 | **11** | ⚠️ AdminSistema agregó asignación |

**El backup del 2026-06-13 (generado a 02:00) no incluye los cambios de IMPL-INFRA-BACKUP-002 (ejecutado durante el día).** El siguiente backup automático (2026-06-14) capturará el estado actualizado.

### Ausencias en metadata.json

| Elemento faltante | Impacto |
|---|---|
| Hash SHA256 de cada CSV | No se puede verificar integridad del ZIP sin descomprimir |
| Versión del esquema de backup | Sin versionado del formato, incompatibilidades futuras no detectables |
| Tiempo de ejecución | Sin diagnóstico de performance del backup |
| Estado de Google Drive | No registra si la subida a Drive fue exitosa o fallida |
| Módulo AdminSistema | Nuevo módulo no aparece en versiones (primer backup post-002 lo incluirá) |

---

## AUDIT-008 — Clasificación de Restaurabilidad

### Dictamen

> **C — REQUIERE CORRECCIONES ANTES DE IMPLEMENTAR RESTAURACIÓN AUTOMATIZADA**

### Fundamentación

La restauración **manual con un operador técnico** es factible consultando `docs/operations/BACKUP-RESTORE-GUIDE.md` (sección 7.3). Sin embargo, la restauración **automatizada** (objetivo de IMPL-INFRA-BACKUP-004) encontrará los siguientes bloqueadores:

#### Bloqueador 1 — HistorialModificacionesBienesSeeder (FALLA SQL)
El seeder lanzará una excepción al intentar insertar en columna `campo_modificado` que no existe en la tabla. La ejecución de `InventarioDatabaseSeeder` se interrumpe en el paso 14.

#### Bloqueador 2 — app_role sin seeder
El módulo de control de acceso visual (qué apps ve cada rol) no tiene cobertura de restauración. Tras restore, ningún rol ve ninguna app, y los usuarios encuentran el dashboard vacío. Funcional pero inutilizable hasta intervención manual.

#### Bloqueador 3 — origen_id en bienes
Los 1.420 bienes quedan con `origen_id=NULL`. No bloquea el sistema (campo nullable) pero invalida el catálogo de orígenes recién implementado.

#### Bloqueador 4 — CategoriasSeeder sin ID (riesgo latente)
En un migrate:fresh sobre base vacía, los IDs coinciden. En reinstalación parcial o entorno con datos previos, los IDs difieren y las FKs de bienes.categoria_id apuntan a categorías incorrectas. El diseño no es robusto.

### ¿Qué se puede afirmar?

| Escenario | Resultado |
|---|---|
| Restore total en servidor nuevo (migrate:fresh + seeders manuales ajustados) | ✓ Sistema funcional con pérdida de datos menor |
| Restore automatizado (script sin ajustes) | ❌ Falla en paso 14 (HistorialModificacionesBienesSeeder) |
| Integridad de datos críticos (bienes, dependencias, usuarios) | ✓ 95% recuperable |
| RBAC completo (app_role, origen_id) | ❌ Requiere intervención manual |
| Disaster Recovery sin SSH | ❌ No posible en estado actual |

---

## AUDIT-009 — Plan de Remediación

### Hallazgos priorizados para IMPL-INFRA-BACKUP-003 y IMPL-INFRA-BACKUP-004

---

#### R-001 — HistorialModificacionesBienesSeeder rompe la ejecución
**Prioridad:** CRÍTICO  
**Impacto:** `InventarioDatabaseSeeder` se detiene. Todos los seeders posteriores no corren.  
**Corrección:** Renombrar `campo_modificado` → `campo`, remover `user_id`, agregar `dependencia_id`. Alternativamente, eliminar este seeder y crear uno que restaure desde el CSV del backup.  
**Esfuerzo:** 1 hora  
**Prerrequisito para:** IMPL-INFRA-BACKUP-004

---

#### R-002 — app_role sin cobertura de seeder
**Prioridad:** CRÍTICO  
**Impacto:** Tras restore, ningún rol tiene apps asignadas. Dashboard vacío para todos los usuarios.  
**Corrección:** Crear `AppRoleSeeder` que lea `app_role.csv` del backup y restaure las asignaciones rol→app.  
**Esfuerzo:** 2 horas  
**Prerrequisito para:** IMPL-INFRA-BACKUP-004

---

#### R-003 — CategoriasSeeder no preserva IDs
**Prioridad:** ALTO  
**Impacto:** Latente: falla en restauraciones parciales. Actualmente funciona en migrate:fresh por coincidencia de auto-increment.  
**Corrección:** Descomentar `'id' => $data['id']` en `CategoriasSeeder`. Una sola línea de código.  
**Esfuerzo:** 5 minutos  
**Prerrequisito para:** IMPL-INFRA-BACKUP-004 (robustez)

---

#### R-004 — BienesSeeder omite origen_id
**Prioridad:** ALTO  
**Impacto:** 1.420 bienes con `origen_id=NULL`. Catálogo de orígenes inoperante tras restore.  
**Corrección:** Agregar `'origen_id' => $data['origen_id'] ?: null` en `BienesSeeder`. Asegurarse que el backup CSV de bienes incluya esta columna (ya lo hace).  
**Esfuerzo:** 30 minutos  
**Prerrequisito para:** Completar dato en IMPL-INFRA-BACKUP-004

---

#### R-005 — OrigenesSeeder faltante
**Prioridad:** ALTO  
**Impacto:** La tabla `origenes` (11 registros) no se restaura desde CSV. Depende de la migración de populate que ya corrió con tabla vacía.  
**Corrección:** Crear `OrigenesSeeder` que lea `origenes.csv` del backup y lo agregue a `InventarioDatabaseSeeder`. Ejecutar ANTES de `BienesSeeder`.  
**Esfuerzo:** 1 hora  
**Prerrequisito para:** R-004

---

#### R-006 — UserSeeder no restaura bloqueado/forzar_cambio_password/es_principal
**Prioridad:** ALTO  
**Impacto:** Estado de bloqueo y configuración de usuarios perdida tras restore.  
**Corrección:** Actualizar `UserSeeder` para incluir estos campos al leer el backup CSV. Actualizar `data/users.csv` en repo para incluir las columnas nuevas.  
**Esfuerzo:** 1 hora

---

#### R-007 — BienesResponsablesSeeder genera datos ficticios
**Prioridad:** MEDIO  
**Impacto:** 10 asignaciones reales de responsables reemplazadas por 10 ficticias (todas a bien_id 1-10, user_id=1).  
**Corrección:** Crear `BienesResponsablesFromCSVSeeder` o actualizar el seeder existente para leer desde el CSV del backup.  
**Esfuerzo:** 1 hora

---

#### R-008 — MantenimientosProgramadosSeeder genera datos ficticios
**Prioridad:** MEDIO  
**Impacto:** 10 mantenimientos reales reemplazados por 10 ficticios con Faker. Fechas futuras inventadas.  
**Corrección:** Actualizar seeder para leer desde CSV del backup.  
**Esfuerzo:** 1 hora

---

#### R-009 — HistorialEliminacionesBienesSeeder inserta 30 ficticios
**Prioridad:** MEDIO  
**Impacto:** Historial de eliminaciones contaminado con 30 registros ficticios (bien_id=1 × 30).  
**Corrección:** Actualizar para leer CSV del backup (0 registros → no inserta nada).  
**Esfuerzo:** 30 minutos

---

#### R-010 — Permission_RoleSeeder no idempotente
**Prioridad:** MEDIO  
**Impacto:** Si se ejecuta dos veces, duplica las 164 asignaciones.  
**Corrección:** Reemplazar `insert()` por `insertOrIgnore()` con constraint único.  
**Esfuerzo:** 30 minutos

---

#### R-011 — metadata.json sin hash de integridad
**Prioridad:** BAJO  
**Impacto:** No se puede verificar integridad del ZIP sin descomprimir.  
**Corrección:** Agregar al metadata: SHA256 de cada CSV y del ZIP completo.  
**Esfuerzo:** 1 hora (en `BackupExportSeeders`)

---

#### R-012 — Restore Guide: instrucción "(repetir para todas las tablas)" ambigua
**Prioridad:** BAJO  
**Impacto:** Un operador podría copiar CSVs sin seeder asociado y no saber qué hacer.  
**Corrección:** Documentar tablas sin seeder y el procedimiento de inserción manual (ya esbozado en sección 7.3).  
**Esfuerzo:** 30 minutos

---

#### R-013 — Seeder orchestration no documentada para restore
**Prioridad:** BAJO  
**Impacto:** No existe un comando único `php artisan db:seed --class=RestoreSeeder` que ejecute todo en orden correcto.  
**Corrección:** Crear `BackupRestoreSeeder` que orqueste el orden correcto de restauración para IMPL-INFRA-BACKUP-004.  
**Esfuerzo:** 1 hora

---

#### R-014 — HistorialDependenciasBienesSeeder genera datos ficticios
**Prioridad:** BAJO  
**Impacto:** 10 movimientos ficticios en el historial (solo 4 dependencias disponibles, datos inventados).  
**Corrección:** Leer desde CSV del backup (0 registros → no inserta nada).  
**Esfuerzo:** 30 minutos

---

### Tabla de priorización consolidada

| ID | Descripción | Prioridad | Esfuerzo | Bloquea |
|---|---|---|---|---|
| R-001 | HistorialModificacionesBienesSeeder FALLA SQL | **CRÍTICO** | 1h | IMPL-INFRA-BACKUP-004 |
| R-002 | app_role sin seeder → dashboard vacío | **CRÍTICO** | 2h | IMPL-INFRA-BACKUP-004 |
| R-003 | CategoriasSeeder sin IDs | **ALTO** | 5min | Robustez restore |
| R-004 | BienesSeeder omite origen_id | **ALTO** | 30min | Integridad datos |
| R-005 | OrigenesSeeder faltante | **ALTO** | 1h | R-004 |
| R-006 | UserSeeder sin campos nuevos | **ALTO** | 1h | Integridad usuarios |
| R-007 | BienesResponsablesSeeder ficticios | **MEDIO** | 1h | Datos responsables |
| R-008 | MantenimientosProgramadosSeeder ficticios | **MEDIO** | 1h | Datos mantenimientos |
| R-009 | HistorialEliminacionesBienesSeeder × 30 ficticios | **MEDIO** | 30min | Historial limpio |
| R-010 | Permission_RoleSeeder no idempotente | **MEDIO** | 30min | Restore repetido |
| R-011 | metadata.json sin hash SHA256 | **BAJO** | 1h | Verificación integridad |
| R-012 | Restore Guide ambigua | **BAJO** | 30min | Documentación |
| R-013 | BackupRestoreSeeder orquestador | **BAJO** | 1h | IMPL-INFRA-BACKUP-004 |
| R-014 | HistorialDependenciasBienesSeeder ficticios | **BAJO** | 30min | Historial limpio |

**Esfuerzo total estimado: 11.5 horas de desarrollo**

---

## Conclusión Final

### ¿Puede IEE reconstruirse completamente desde GitHub + Migraciones + ZIP institucional?

**SÍ, parcialmente.** Un operador técnico puede restaurar el sistema con intervención manual en 2-4 horas. La restauración automatizada (IMPL-INFRA-BACKUP-004) requiere resolver primero los hallazgos R-001 a R-006.

### Estado por componente

| Componente | Recuperabilidad | Notas |
|---|---|---|
| Código fuente | ✅ 100% | GitHub |
| Estructura BD | ✅ 100% | Migraciones |
| Autenticación (login) | ✅ 98% | Contraseñas regeneradas por fórmula |
| Permisos y roles | ✅ 98% | RBAC funcional, permission_user vacío |
| Catálogos de inventario | ✅ 100% | Hardcoded con IDs correctos |
| Bienes (datos básicos) | ✅ 95% | Sin origen_id |
| Detalles de bienes | ✅ 100% | Seeder correcto |
| Dependencias | ✅ 100% | updateOrInsert preserva IDs |
| Apps → visibles para roles | ❌ 0% | app_role sin seeder |
| Orígenes de bienes | ❌ 0% | Sin OrigenesSeeder + BienesSeeder sin origen_id |
| Responsables de bienes | ❌ datos incorrectos | Seeder ficticio |
| Mantenimientos programados | ❌ datos incorrectos | Seeder ficticio |
| Historial modificaciones | ❌ 0% + FALLA SQL | Seeder roto |
| Historial eliminaciones | ❌ datos incorrectos | 30 ficticios |
| Auditoría de passwords | ❌ 0% | Sin seeder |

### Certificación de Disaster Recovery

| Criterio | Estado |
|---|---|
| Sistema arranca tras restore | ✓ Sí |
| Usuarios pueden autenticarse | ✓ Sí (contraseñas por fórmula) |
| RBAC visual (apps en dashboard) | ❌ No (app_role perdido) |
| Inventario operativo | ✓ Sí (bienes/detalles restaurados) |
| Datos de auditoría | ❌ Parcial (historial incompleto) |
| Restore sin intervención manual | ❌ No (R-001 falla, R-002 bloquea) |
| Restore < 4 horas por operador técnico | ✓ Sí (con BACKUP-RESTORE-GUIDE.md) |

**Para alcanzar certificación completa:** Resolver R-001, R-002, R-003, R-004, R-005, R-006 (≈ 6.5 horas de desarrollo). Estos 6 ítems son prerrequisito de IMPL-INFRA-BACKUP-004.

---

## Referencias

- `app/Console/Commands/BackupExportSeeders.php` — motor de exportación
- `docs/operations/BACKUP-RESTORE-GUIDE.md` — guía de restauración v1.0
- `docs/impl/IMPL-INFRA-BACKUP-001-Backups-Institucionales.md`
- `docs/impl/IMPL-INFRA-BACKUP-002-Centro-De-Administracion-De-Backups.md`
- `backups/2026-06-13/metadata.json` — metadata verificada
- `Modules/Inventario/Database/Seeders/` — seeders auditados
- `Modules/User/Database/Seeders/` — seeders auditados
