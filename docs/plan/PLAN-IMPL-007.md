# PLAN-IMPL-007 — Initial Inventory Data Load

**Estado:** Aprobado
**Fecha:** 2026-06-08
**Relacionado con:** PMP-001, ROADMAP-001 Fase 2, BASELINE-001 R-05/DT-005

---

## 1. Antecedentes

BASELINE-001 identificó que el módulo Inventario estaba técnicamente completo
pero sin datos operativos (R-05 / DT-005). Todos los catálogos requeridos
(categorías, estados, almacenamientos, ubicaciones, dependencias) estaban vacíos.

La inspección previa (sesión IMPL-007 prep) identificó y corrigió tres bugs en
los seeders antes de autorizar la ejecución:

* **B1** — `DependenciasSeeder`: columna `user_id` → `usuario_id`
* **B2** — `BienesSeeder`: nested `foreach` corregido a loop único
* **B3** — `CategoriasSeeder`: nested `foreach` corregido a loop único

---

## 2. Objetivo

Ejecutar `InventarioDatabaseSeeder` para cargar la base de datos con los datos
institucionales reales de inventario, habilitando la operación del módulo.

---

## 3. Datos a cargar

| Seeder | Fuente | Registros esperados |
|---|---|---|
| `AlmacenamientosSeeder` | Inline | 2 |
| `CategoriasSeeder` | `data/categorias.csv` | 28 |
| `EstadosSeeder` | Inline | 4 |
| `MantenimientosSeeder` | Inline | 3 |
| `UbicacionesSeeder` | Inline | 4 |
| `DependenciasSeeder` | `data/dependencias.csv` | 135 |
| `BienesSeeder` | `data/bienes.csv` | 1,420 |

---

## 4. Prerequisitos cumplidos

* ✅ `bienes.precio` es `DECIMAL(12,2)` — IMPL-004
* ✅ `auth:sanctum` activo en API — AUDIT-004
* ✅ Bugs B1/B2/B3 corregidos — sesión preparatoria
* ✅ 0 registros soft-deleted en bienes.csv — sin impacto en B5
* ✅ Todos los `user_id` referenciados en dependencias.csv existen en `users`

---

## 5. Comando de ejecución

```bash
php artisan db:seed \
  --class="Modules\Inventario\Database\Seeders\InventarioDatabaseSeeder" \
  --force
```

---

## 6. Validación post-ejecución

Verificar conteos en cada tabla mediante:

```sql
SELECT 'almacenamientos' as tabla, COUNT(*) FROM almacenamientos
UNION ALL SELECT 'categorias',   COUNT(*) FROM categorias
UNION ALL SELECT 'estados',      COUNT(*) FROM estados
UNION ALL SELECT 'mantenimientos', COUNT(*) FROM mantenimientos
UNION ALL SELECT 'ubicaciones',  COUNT(*) FROM ubicaciones
UNION ALL SELECT 'dependencias', COUNT(*) FROM dependencias
UNION ALL SELECT 'bienes',       COUNT(*) FROM bienes;
```

---

## 7. Criterios de éxito

* Todos los conteos coinciden con los esperados.
* Sin errores de FK en la ejecución.
* `bienes` accesibles desde la UI del módulo Inventario.

---

## 8. Aprobación

Aprobado por Dirección General. Fecha: 2026-06-08.
