# IMPL-004 — Migración de FLOAT a DECIMAL(12,2) en bienes.precio

**Fecha:** 2026-06-08
**Estado:** COMPLETADO
**Módulo:** Inventario
**Plan de referencia:** PLAN-IMPL-004
**Riesgo final:** BAJO

---

## Objetivo

Reemplazar el tipo `FLOAT` del campo `bienes.precio` por `DECIMAL(12,2)` para garantizar exactitud en valores monetarios.

---

## Situación inicial

| Campo | Tipo antes | Tipo después |
|---|---|---|
| `bienes.precio` | `float` | `decimal(12,2)` |

- Registros en `bienes` al momento de la migración: **0**
- No hubo conversión de datos — riesgo de pérdida: ninguno.

---

## Actividades ejecutadas

### A-01 — Verificación de esquema

Confirmado `precio float` en `bienes` con 0 registros.

### A-02 — Creación de migración

Archivo creado:

```
Modules/Inventario/Database/Migrations/2026_06_08_000003_change_precio_float_to_decimal_in_bienes.php
```

Contenido:

```php
Schema::table('bienes', function (Blueprint $table) {
    $table->decimal('precio', 12, 2)->nullable()->change();
});
```

### A-03 — Ejecución

```
php artisan migrate --force
2026_06_08_000003_change_precio_float_to_decimal_in_bienes .... 57.72ms DONE
```

Batch registrado: **4**

### A-04 — Verificación de integridad

```sql
DESCRIBE bienes;
-- precio | decimal(12,2) | YES | NULL
```

### A-05 — Revisión de código

| Componente | Hallazgo | Cambio |
|---|---|---|
| `Entities/Bien.php` | Sin `$casts` para precio | Agregado `'precio' => 'decimal:2'` |
| `Livewire/Bienes/BienesIndex.php` | `nullable\|numeric` — compatible ✅ | Sin cambio |
| `Livewire/Bienes/EditarCampoBien.php` | `input type="number"` — compatible ✅ | Sin cambio |
| `resources/views/.../bienes-index.blade.php` | `type: 'number'` — compatible ✅ | Sin cambio |
| `resources/views/.../editar-campo-bien.blade.php` | `(float) $valor` en format — compatible ✅ | Sin cambio |

### A-06 — Revisión de reportes, exportaciones y actas

| Componente | Hallazgo |
|---|---|
| Actas PDF | Sin referencia directa a `precio` ✅ |
| Exportación CSV | Sin referencia directa a `precio` ✅ |
| HTTP Controllers | Sin referencia directa a `precio` ✅ |

### A-07 — Documentación

Generado este documento `IMPL-004.md`.

---

## Cambios en código

### `Modules/Inventario/Entities/Bien.php`

Añadido cast para garantizar que Eloquent devuelva el precio como string decimal con exactamente 2 decimales:

```php
protected $casts = [
    'precio' => 'decimal:2',
];
```

Esto previene cualquier imprecisión de punto flotante a nivel de PHP al leer valores desde la base de datos.

---

## Archivos creados/modificados

| Archivo | Acción |
|---|---|
| `Modules/Inventario/Database/Migrations/2026_06_08_000003_change_precio_float_to_decimal_in_bienes.php` | Creado |
| `Modules/Inventario/Entities/Bien.php` | Modificado — cast `decimal:2` |
| `docs/impl/IMPL-004.md` | Creado |

---

## Criterios de éxito cumplidos

| Criterio | Estado |
|---|---|
| `precio` es `DECIMAL(12,2)` | ✅ |
| Sin errores funcionales | ✅ |
| Sin regresiones en vistas/validaciones | ✅ |
| Migración registrada en Git | ✅ |
| IMPL-004 generado | ✅ |

---

## Riesgo residual

Ninguno. Los valores monetarios quedan almacenados con precisión exacta.

El `$casts = ['precio' => 'decimal:2']` garantiza consistencia también a nivel de aplicación PHP.
