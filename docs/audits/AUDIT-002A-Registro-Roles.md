# AUDIT-002A — Análisis de Usuarios Afectados por Bug de Registro de Roles

**Fecha de auditoría:** 2026-06-08
**Auditor:** Claude Code (análisis asistido)
**Referencia:** IMPL-002 — Security Hardening, Fix 1
**Estado:** COMPLETADO — Solo análisis. Cero modificaciones realizadas.

---

## Resumen Ejecutivo

Se auditaron los 6 usuarios con roles privilegiados (Rector / Coordinador) para
determinar si fueron creados mediante el formulario de registro público durante el
periodo en que existía el bug de asignación de roles.

**Conclusión:** El bug de registro estuvo presente pero **no fue explotado**.
Los 6 usuarios con roles Rector/Coordinador fueron creados mediante el `UserSeeder`,
no a través del formulario de registro público.

**Evidencia determinante:** La totalidad de los 116 usuarios en la base de datos
tienen `created_at = NULL`. El seeder utiliza `DB::table('users')->insert()` sin
incluir el campo `created_at`, lo que produce timestamps nulos. El formulario de
registro de Fortify utiliza `User::create()` que auto-popula `created_at`. Si
algún usuario hubiera registrado cuenta públicamente, tendría `created_at ≠ NULL`.

**No se requiere acción correctiva sobre roles existentes.**

---

## Contexto del Bug

### Descripción

En `resources/views/auth/register.blade.php` el `<select>` de rol mostraba:

```html
<!-- INCORRECTO (antes de IMPL-002) -->
<option value="2">Docente</option>    <!-- value=2 es Rector en la BD -->
<option value="3">Estudiante</option>  <!-- value=3 es Coordinador en la BD -->
```

En `app/Actions/Fortify/CreateNewUser.php` la validación era:
```php
'role_id' => ['required', 'integer', 'in:2,3']
```

Cualquier usuario que se registrara públicamente obtenía `role_id=2` (Rector)
o `role_id=3` (Coordinador) — roles con acceso privilegiado completo.

### Periodo de exposición

- **Sin git history:** El repositorio no tiene historial de commits, por lo que no
  es posible determinar la fecha exacta en que se introdujo el bug.
- **Estimación:** El bug es estructural (probablemente desde el inicio del proyecto)
  y no surgió de un commit específico identificable.
- **Corrección aplicada:** 2026-06-08 (IMPL-002)

---

## Metodología de Auditoría

### Verificación de origen de usuarios

El seeder `Modules/User/Database/Seeders/UserSeeder.php` crea usuarios así:

```php
DB::table('users')->insert([
    'id'       => $data['id'],
    'nombres'  => $data['nombres'],
    // ...
    // ← 'created_at' NO está incluido → queda NULL en la BD
]);
```

En contraste, el registro público vía Fortify ejecuta `User::create([...])`, que
activa los timestamps automáticos de Eloquent → `created_at` se registra con la
fecha/hora exacta del registro.

### Query de auditoría ejecutada

```sql
SELECT id, nombres, apellidos, email, role_id, created_at
FROM users
WHERE role_id IN (2, 3)
ORDER BY id;
```

```sql
SELECT
    r.nombre AS rol,
    COUNT(u.id) AS total,
    SUM(CASE WHEN u.created_at IS NULL     THEN 1 ELSE 0 END) AS sin_timestamp,
    SUM(CASE WHEN u.created_at IS NOT NULL THEN 1 ELSE 0 END) AS con_timestamp
FROM roles r
LEFT JOIN users u ON u.role_id = r.id
GROUP BY r.id, r.nombre
ORDER BY r.id;
```

**Resultado global:** `116 usuarios — 116 con created_at NULL — 0 con timestamp`.

---

## Hallazgos por Usuario

### Usuario 1 — Adolfo León Ruiz Hernández

| Campo        | Valor                           |
|---|---|
| **id**       | 54                              |
| **email**    | rectoriaiee@entrerrios.edu.co   |
| **userID**   | 71379517                        |
| **Rol**      | Rector (role_id = 2)            |
| **created_at** | NULL                          |
| **Sesiones** | 2 sesiones activas (último acceso: 2026-06-08 11:02) |

**Análisis:**
- Email institucional `@entrerrios.edu.co` — patrón consistente con personal de la IEE.
- Nomenclatura de email (`rectoriaiee`) indica cargo directivo de la institución.
- `created_at = NULL` confirma origen en seeder.
- Sesiones activas recientes → usuario legítimo en uso activo del sistema.

**Clasificación: ✅ LEGÍTIMO**

---

### Usuario 2 — Omaira Quitian Infante

| Campo        | Valor                           |
|---|---|
| **id**       | 52                              |
| **email**    | oquitiani@entrerrios.edu.co     |
| **userID**   | 37926145                        |
| **Rol**      | Coordinador (role_id = 3)       |
| **created_at** | NULL                          |

**Análisis:**
- Email institucional con patrón `iniciales + apellido@entrerrios.edu.co`.
- `created_at = NULL` confirma origen en seeder.
- id=52 es uno de los IDs más bajos → probablemente entre los primeros registros del CSV.

**Clasificación: ✅ LEGÍTIMO**

---

### Usuario 3 — Luis Fernando Ochoa Muñoz

| Campo        | Valor                           |
|---|---|
| **id**       | 53                              |
| **email**    | lfochoam@entrerrios.edu.co      |
| **userID**   | 71655142                        |
| **Rol**      | Coordinador (role_id = 3)       |
| **created_at** | NULL                          |

**Análisis:**
- Email institucional con patrón estándar.
- `created_at = NULL` confirma origen en seeder.
- id consecutivo a id=52, consistente con inserción por CSV.

**Clasificación: ✅ LEGÍTIMO**

---

### Usuario 4 — Andrés David Escobar Zapata

| Campo        | Valor                           |
|---|---|
| **id**       | 70                              |
| **email**    | adescobarz@entrerrios.edu.co    |
| **userID**   | 71339991                        |
| **Rol**      | Coordinador (role_id = 3)       |
| **created_at** | NULL                          |

**Análisis:**
- Email institucional con patrón estándar.
- `created_at = NULL` confirma origen en seeder.
- Gap de id entre 53 y 70 sugiere que el CSV cargó usuarios por secciones (docentes
  primero, luego coordinadores adicionales).

**Clasificación: ✅ LEGÍTIMO**

---

### Usuario 5 — Nidia Omaira Herrera Espinosa

| Campo        | Valor                           |
|---|---|
| **id**       | 101                             |
| **email**    | noherrerae@entrerrios.edu.co    |
| **userID**   | 32228303                        |
| **Rol**      | Coordinador (role_id = 3)       |
| **created_at** | NULL                          |

**Análisis:**
- Email institucional con patrón estándar.
- `created_at = NULL` confirma origen en seeder.

**Clasificación: ✅ LEGÍTIMO**

---

### Usuario 6 — Dorian Rodrigo Ruiz Hernández

| Campo        | Valor                           |
|---|---|
| **id**       | 115                             |
| **email**    | dorianrodrigo@gmail.com         |
| **userID**   | 71481707                        |
| **Rol**      | Coordinador (role_id = 3)       |
| **created_at** | NULL                          |

**Análisis:**
- Único email no institucional (`@gmail.com`) — coincide con el administrador/propietario
  del sistema.
- `created_at = NULL` confirma origen en seeder.
- id=115 (de los más altos) sugiere que fue añadido manualmente al CSV como cuenta
  de administración técnica.

**Nota:** Este usuario tiene acceso técnico-administrativo al sistema. El rol
Coordinador le fue asignado deliberadamente por el propietario del proyecto.

**Clasificación: ✅ LEGÍTIMO**

---

## Tabla Resumen

| id  | Nombre                        | Email                         | Rol         | created_at | Origen   | Clasificación   |
|-----|-------------------------------|-------------------------------|-------------|------------|----------|-----------------|
| 54  | Adolfo León Ruiz Hernández    | rectoriaiee@entrerrios.edu.co | Rector      | NULL       | Seeder   | ✅ LEGÍTIMO     |
| 52  | Omaira Quitian Infante        | oquitiani@entrerrios.edu.co   | Coordinador | NULL       | Seeder   | ✅ LEGÍTIMO     |
| 53  | Luis Fernando Ochoa Muñoz     | lfochoam@entrerrios.edu.co    | Coordinador | NULL       | Seeder   | ✅ LEGÍTIMO     |
| 70  | Andrés David Escobar Zapata   | adescobarz@entrerrios.edu.co  | Coordinador | NULL       | Seeder   | ✅ LEGÍTIMO     |
| 101 | Nidia Omaira Herrera Espinosa | noherrerae@entrerrios.edu.co  | Coordinador | NULL       | Seeder   | ✅ LEGÍTIMO     |
| 115 | Dorian Rodrigo Ruiz Hernández | dorianrodrigo@gmail.com       | Coordinador | NULL       | Seeder   | ✅ LEGÍTIMO     |

---

## Distribución Total de Roles

| Rol           | role_id | Usuarios | sin created_at | con created_at |
|---------------|---------|----------|----------------|----------------|
| Administrador | 1       | 0        | —              | —              |
| Rector        | 2       | 1        | 1 (100%)       | 0              |
| Coordinador   | 3       | 5        | 5 (100%)       | 0              |
| Auxiliar      | 4       | 28       | 28 (100%)      | 0              |
| Docente       | 5       | 82       | 82 (100%)      | 0              |
| Estudiante    | 6       | 0        | —              | —              |
| Invitado      | 7       | 0        | —              | —              |
| **Total**     |         | **116**  | **116 (100%)** | **0**          |

**Observación adicional:** No existe ningún usuario con rol Estudiante (role_id=6).
Todos los 82 usuarios de bajo privilegio tienen rol Docente. El sistema nunca fue
usado por estudiantes como usuarios registrados.

---

## Riesgo Estimado

| Riesgo                                  | Nivel    | Estado    |
|-----------------------------------------|----------|-----------|
| Explotación pasada del bug              | CRÍTICO  | ✅ No ocurrió |
| Usuarios Rector/Coordinador ilegítimos  | ALTO     | ✅ Ninguno encontrado |
| Cuentas de terceros con acceso          | MEDIO    | ✅ No aplica |
| Exposición futura del registro público  | ALTO     | ✅ Corregido (IMPL-002) |

---

## Recomendaciones

### Inmediatas (ya implementadas)

1. ✅ El bug en `register.blade.php` fue corregido (IMPL-002) — valores del select
   apuntan ahora a Docente (5) y Estudiante (6).
2. ✅ La validación en `CreateNewUser.php` fue corregida a `in:5,6`.
3. ✅ Rate limiting de registro activado: 3 req/min por IP.

### Recomendaciones adicionales

4. **Activar timestamps en el seeder** — Agregar `'created_at' => now()` en el
   método `DB::table()->insert()` para que futuros re-seeders dejen trazabilidad.
   *(No urgente — el seeder se ejecuta solo en instalaciones limpias.)*

5. **Registrar IP de creación** — Considerar añadir campo `created_by_ip` o
   mantener un log de auditoría separado para registros de nuevos usuarios.
   *(Mejora de trazabilidad futura.)*

6. **Verificación manual opcional** — Comparar los 6 usuarios con Rector/Coordinador
   contra la nómina oficial de la IEE para confirmar que corresponden a personal
   activo. Esto no es estrictamente necesario dado el nivel de evidencia técnica,
   pero es buena práctica para instituciones educativas.

---

## Limitaciones del Análisis

- **Sin git history:** No es posible determinar la fecha exacta en que se introdujo
  el bug de registro. El repositorio no tiene historial de commits.
- **Sin logs de Fortify:** El sistema registra logs a nivel `error`, no `info`.
  Los intentos de registro exitosos no dejan rastro en `storage/logs/laravel.log`.
- **`created_at = NULL` como evidencia:** El método de inferencia es robusto
  (basado en el mecanismo técnico del seeder vs. Fortify), pero es indirecto.
  No existe log directo que registre "este usuario fue creado por el seeder".

---

*Auditoría de solo lectura. Cero modificaciones realizadas en la base de datos.*
*Referencia: `docs/impl/IMPL-002-Security-Hardening.md`*
