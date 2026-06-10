# AUDIT-INV-004A — Bienes Images Readiness Assessment

**Fecha:** 2026-06-09  
**Repositorio:** private/bhagamappsModular  
**Módulo:** Modules/Inventario  
**Propósito:** Determinar el estado real de la infraestructura de imágenes antes de IMPL-INV-004

---

## Veredicto

**B) REQUIERE INFRAESTRUCTURA PREVIA**

La base de datos y el modelo existen, pero hay un mismatch crítico modelo/esquema y faltan componente Livewire, vistas, permisos, lógica de almacenamiento y rutas. La implementación no es funcional en su estado actual.

**Nivel de preparación: 4 / 10 puntos**

---

## Hallazgos por checkpoint

### 1. Tabla `bienes_imagenes`

**Estado: ENCONTRADA**

- **Migración:** `Modules/Inventario/Database/Migrations/2025_05_21_020619_create_bienes_imagenes_table.php`
- **Esquema:**

```php
Schema::create('bienes_imagenes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('bien_id')->constrained('bienes')->onDelete('cascade');
    $table->string('ruta_imagen');   // ← columna real en BD
    $table->string('descripcion')->nullable();
    $table->timestamps();
});
```

- ✅ FK correcta con cascade delete
- ✅ Campos suficientes para MVP

---

### 2. Migraciones asociadas

**Estado: ENCONTRADAS**

21 migraciones en `Modules/Inventario/Database/Migrations/`. La única relacionada con imágenes es la `#13`:

| # | Archivo | Relevancia |
|---|---|---|
| 13 | `2025_05_21_020619_create_bienes_imagenes_table.php` | ✅ Tabla de imágenes |
| 21 | `2026_06_09_000009_add_responsables_permissions.php` | Más reciente |

No hay migraciones de permisos de imágenes ni de columnas adicionales.

---

### 3. Modelo `BienImagen`

**Estado: ENCONTRADO — CON MISMATCH CRÍTICO**

- **Archivo:** `Modules/Inventario/Entities/BienImagen.php`

```php
protected $fillable = [
    'bien_id',
    'ruta',          // ⚠️ INCORRECTO — la columna en BD se llama 'ruta_imagen'
    'descripcion'
];

public function bien()
{
    return $this->belongsTo(Bien::class, 'bien_id');
}
```

**Problema:** El fillable declara `'ruta'` pero la columna en la BD es `'ruta_imagen'`. Cualquier operación de mass assignment fallará silenciosamente.

**Faltante en el modelo:**
- Sin accessor para URL pública (`getUrlAttribute`)
- Sin cast de fecha
- Sin scope para imagen principal
- Sin constante de disco de almacenamiento

---

### 4. Relaciones con `Bien`

**Estado: ENCONTRADA**

- **Archivo:** `Modules/Inventario/Entities/Bien.php` (líneas 91–94)

```php
public function imagenes()
{
    return $this->hasMany(BienImagen::class);
}
```

- ✅ Relación correcta
- ❌ Sin relación `imagenPrincipal()` (hasOne con ordenamiento)
- ❌ Sin eager loading en `BienesIndex::filtrarBienesQuery()`

---

### 5. Permisos existentes

**Estado: AUSENTES**

Búsqueda en todas las migraciones y en `AuthServiceProvider.php`: **cero resultados** para permisos de imágenes.

No existen permisos del tipo:
- `ver-imagenes-bienes`
- `subir-imagenes-bienes`
- `eliminar-imagenes-bienes`

Los gates actuales en `AuthServiceProvider` cubren catálogos y responsables únicamente.

---

### 6. Componentes Livewire existentes

**Estado: AUSENTES**

Componentes actuales en `Modules/Inventario/Livewire/`:

```
Actas/: ActaEntregaIndex, ActaPDF, ActaPrinter
Bienes/: BienesIndex, EditarCampoBien, EditarDetalleBien, EditarDetalleBienModal
Catalogos/: 7 componentes de catálogos
Heb/Hmb/: 4 componentes de notificaciones
Responsables/: ResponsablesIndex
```

**No existe ningún componente de imágenes.**

`BienesIndex.php` no tiene:
- Trait `WithFileUploads`
- Columna `imagenes` en `availableColumns`
- Lógica de upload, preview o eliminación de imágenes

---

### 7. Vistas existentes

**Estado: AUSENTES**

40 archivos Blade en `Modules/Inventario/resources/views/`. Ninguno relacionado con imágenes.

`bienes-index.blade.php` (1122 líneas): cero ocurrencias de "imagen", "image", "foto", "photo", "thumbnail".

No existe ninguna vista de:
- Galería de imágenes por bien
- Upload de imágenes
- Preview / lightbox
- Carrusel

---

### 8. Estrategia de almacenamiento

**Estado: PARCIAL**

- **`config/filesystems.php`:** discos `local`, `public` (→ `storage/app/public`) y `s3` configurados.
- **FILESYSTEM_DISK=local** (variables de entorno)
- **AWS:** credenciales vacías en `.env.example`
- Directorios `storage/app/` y `storage/app/public/` existen.

**Faltante:**
- Sin disco personalizado `bienes` o `inventario`
- Sin convención de paths definida en código
- Sin seeder de symlink verificado

---

### 9. Integración con filesystem

**Estado: AUSENTE**

Búsqueda en todo `Modules/Inventario/` (`--include="*.php"`):
- `Storage::` → 0 resultados
- `WithFileUploads` → 0 resultados
- `TemporaryUploadedFile` → 0 resultados

El seeder `BienesImagenesSeeder` usa rutas dummy (`'imagenes/' . $faker->uuid . '.jpg'`) — no hay lógica real de almacenamiento.

---

### 10. Integración con backups

**Estado: AUSENTE**

- `composer.json`: sin `spatie/laravel-backup`
- `config/`: sin `backup.php`
- Las imágenes subidas **no serían respaldadas** por ningún mecanismo actual

---

## Tabla resumen

| # | Checkpoint | Estado | Bloquea IMPL-INV-004 |
|---|---|---|---|
| 1 | Tabla `bienes_imagenes` | ✅ Encontrada | No |
| 2 | Migraciones asociadas | ✅ Encontradas | No |
| 3 | Modelo `BienImagen` | ⚠️ Mismatch crítico `ruta` vs `ruta_imagen` | **Sí** |
| 4 | Relación `Bien::imagenes()` | ✅ Encontrada | No |
| 5 | Permisos de imágenes | ❌ Ausentes | **Sí** |
| 6 | Componente Livewire | ❌ Ausente | **Sí** |
| 7 | Vistas | ❌ Ausentes | **Sí** |
| 8 | Estrategia de almacenamiento | ⚠️ Parcial | **Sí** |
| 9 | Integración filesystem | ❌ Ausente | **Sí** |
| 10 | Integración backups | ❌ Ausente | No (recomendado) |

---

## Bloqueadores para IMPL-INV-004

En orden de prioridad:

**B-001 — CRÍTICO:** Mismatch `BienImagen::fillable` → `'ruta'` vs columna BD `'ruta_imagen'`.
Corregir antes de cualquier implementación.

**B-002 — REQUERIDO:** Sin permisos de imágenes. Necesaria migración con: `ver/subir/eliminar-imagenes-bienes`.

**B-003 — REQUERIDO:** Sin componente Livewire. Se requiere componente con `WithFileUploads` para gestión de imágenes.

**B-004 — REQUERIDO:** Sin vistas de galería, upload y preview.

**B-005 — REQUERIDO:** Sin lógica de almacenamiento (Storage, paths, validación de archivos).

**B-006 — RECOMENDADO:** Sin disco personalizado para imágenes de bienes.

**B-007 — RECOMENDADO:** Sin integración de backups para archivos subidos.

---

## Infraestructura existente reutilizable

Lo siguiente NO necesita crearse desde cero:

- ✅ Tabla `bienes_imagenes` (solo corregir mismatch en modelo)
- ✅ Modelo `BienImagen` (corregir fillable + agregar accessor URL)
- ✅ Relación `Bien::imagenes()` en Bien.php
- ✅ Patrón de permisos: ya establecido en `add_responsables_permissions.php`
- ✅ Patrón Livewire: `ResponsablesIndex` como referencia de arquitectura
- ✅ `storage/app/public/` disponible para imágenes

---

## Recomendaciones para IMPL-INV-004

1. **Corregir mismatch:** `BienImagen::fillable` → cambiar `'ruta'` por `'ruta_imagen'`
2. **Migración de permisos:** `ver/subir/eliminar-imagenes-bienes` + asignación de roles
3. **Gates en AuthServiceProvider:** agregar los 3 nuevos permisos
4. **Componente `ImagenesBien`:** Livewire con `WithFileUploads`, validación mime/size, preview, eliminación
5. **Vista `livewire/bienes/imagenes-bien.blade.php`:** galería + uploader inline
6. **Storage path:** `storage/app/public/bienes/{bien_id}/` con `storage:link` verificado
7. **Integración BienesIndex:** columna de miniatura opcional (toggle)
