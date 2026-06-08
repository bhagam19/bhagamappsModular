# IMPL-003 — PermissionRole Cleanup

**Fecha:** 2026-06-08
**Referencia:** Auditoría AUDIT-003 + Plan de Fase 2 (IMPL-002)
**Estado:** Completado

---

## Problema detectado

La tabla `permission_role` contenía **156 registros** pero solo **80 combinaciones únicas**
de `(role_id, permission_id)`. Los 76 registros restantes eran duplicados exactos, resultado
de ejecutar el seeder `Permission_RoleSeeder` dos veces en producción.

La tabla no tenía constraint `UNIQUE` sobre `(role_id, permission_id)`, por lo que las
inserciones repetidas no generaban error.

**Impacto funcional:** Ninguno en la lógica actual. El método `hasPermission()` usa
`->where('slug', $slug)->exists()` que retorna `true` con cualquier cantidad de registros.
Sin embargo, los duplicados:
- Complicaban auditorías y debugging del sistema de permisos.
- Podían causar problemas de rendimiento con crecimientos futuros.
- Impedían usar `firstOrCreate` seguro en seeders futuros sin protección manual.

---

## Riesgo

| Riesgo                       | Nivel    | Mitigación aplicada |
|------------------------------|----------|---------------------|
| Pérdida accidental de permisos | ALTO    | Backup completo previo + validación post-ejecución |
| Romper autenticación/autorización | ALTO | Validación de `hasPermission()` en usuarios reales post-migración |
| Error en constraint si quedan duplicados | ALTO | Migración 1 ejecutada antes de migración 2 |
| Anomalías no detectadas | MEDIO | Dry-run confirmó exactamente 76 registros a eliminar |

---

## Solución aplicada

### Decisión

Se conserva el registro con `id` más bajo para cada combinación `(role_id, permission_id)`.
La lógica `MIN(id)` garantiza preservar los registros originales de la primera ejecución del seeder.

Para las 4 combinaciones de Coordinador con una sola ocurrencia
(ver/crear/editar/eliminar-usuarios, ids 130-133): no se eliminan, son permisos legítimos
añadidos en `Users-v1.1.0`.

### Proceso ejecutado

1. **Auditoría previa** → `docs/audits/AUDIT-003-PermissionRole-Duplicates.md`
2. **Respaldo lógico** → `docs/impl/backups/permission_role_before_cleanup.sql` (156 INSERTs)
3. **Dry-run** → confirmado: 76 registros a eliminar, 0 anomalías
4. **Migración 1** → eliminación de duplicados
5. **Validación intermedia** → 80 registros, 0 duplicados ✅
6. **Migración 2** → constraint UNIQUE aplicado
7. **Validación final** → RBAC funcional, permisos intactos ✅

---

## Migraciones creadas

### Migración 1 — Limpieza de duplicados

**Archivo:** `database/migrations/2026_06_08_000001_clean_permission_role_duplicates.php`

```sql
DELETE pr1
FROM permission_role pr1
INNER JOIN permission_role pr2
    ON  pr1.role_id       = pr2.role_id
    AND pr1.permission_id = pr2.permission_id
    AND pr1.id            > pr2.id
```

**Características:**
- Idempotente: si no hay duplicados, la sentencia no afecta ningún registro.
- No usa `TRUNCATE` ni re-inserción: solo elimina los sobrantes.
- Conserva los ids más bajos (primer seeder).

### Migración 2 — Constraint UNIQUE

**Archivo:** `database/migrations/2026_06_08_000002_add_unique_constraint_to_permission_role.php`

```sql
ALTER TABLE `permission_role`
ADD UNIQUE KEY `permission_role_unique` (`role_id`, `permission_id`);
```

**Características:**
- Previene duplicados futuros a nivel de base de datos.
- El seeder puede ejecutarse N veces sin generar duplicados si usa `firstOrCreate` o `insertOrIgnore`.
- Reversible: `down()` elimina el constraint.

---

## Registros eliminados

| Rol           | Duplicados eliminados | IDs eliminados |
|---------------|-----------------------|----------------|
| Administrador | 26 | 78–103 |
| Rector        | 26 | 104–129 |
| Coordinador   | 8  | 134–141 |
| Auxiliar      | 8  | 150–157 |
| Docente       | 8  | 142–149 |
| **Total**     | **76** | |

---

## Restricción agregada

```
permission_role.permission_role_unique UNIQUE (role_id, permission_id)
```

Verificación post-migración:
```
Table            Non_unique  Key_name                   Columns
permission_role  0           permission_role_unique     role_id, permission_id
```

---

## Validaciones realizadas

### Conteo final

| Métrica                | Antes | Después | Esperado |
|------------------------|-------|---------|----------|
| Total registros        | 156   | 80      | 80       |
| Combinaciones únicas   | 80    | 80      | 80       |
| Duplicados             | 76    | 0       | 0        |

### Permisos por rol (intactos)

| Rol           | Permisos activos | Estado |
|---------------|-----------------|--------|
| Administrador | 26              | ✅     |
| Rector        | 26              | ✅     |
| Coordinador   | 12              | ✅     |
| Auxiliar      | 8               | ✅     |
| Docente       | 8               | ✅     |

### Validación funcional de hasPermission()

```
Rector (rectoriaiee@entrerrios.edu.co):
  ver-bienes: SI               ✅
  ver-usuarios: SI             ✅
  aprobar-pendientes-bienes: SI ✅
  permiso-inventado: NO        ✅

Coordinador (oquitiani@entrerrios.edu.co):
  ver-bienes: SI               ✅
  ver-usuarios: SI             ✅
  ver-roles: NO                ✅ (no asignado a Coordinador)
```

---

## Resultado final

- **76 duplicados eliminados** — tabla reducida de 156 a 80 registros.
- **Constraint UNIQUE aplicado** — imposible insertar duplicados futuros.
- **RBAC 100% funcional** — todos los permisos correctamente asignados.
- **Backup disponible** — `docs/impl/backups/permission_role_before_cleanup.sql`.

---

## Procedimiento de restauración (si fuera necesario)

```bash
mysql -u adolfo_bdModular -p adolfo_bhagamappsModular \
  < docs/impl/backups/permission_role_before_cleanup.sql
```

Luego revertir las migraciones:

```bash
php artisan migrate:rollback --path=database/migrations/2026_06_08_000002_add_unique_constraint_to_permission_role.php
php artisan migrate:rollback --path=database/migrations/2026_06_08_000001_clean_permission_role_duplicates.php
```
