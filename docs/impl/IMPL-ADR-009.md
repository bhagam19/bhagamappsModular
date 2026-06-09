# IMPL-ADR-009 — Remove Unused app_user.role_id

**Fecha:** 2026-06-08
**Estado:** Ejecutado
**Prioridad:** Media
**Relacionado con:** ADR-009, AUDIT-APPS-003, AUDIT-APPS-004, ADR-008 (DP-001), IMPL-013

---

## 1. V-001 — Auditoría de referencias

Búsqueda global previa a la eliminación. Resultados:

### `app_user` en el código

| Archivo | Línea | Contexto |
|---------|-------|---------|
| `Modules/Apps/Entities/App.php:36` | `belongsToMany(...'app_user'...)` | Relación pivot — `withPivot('activo')` únicamente |
| `Modules/Apps/Entities/App.php:67` | `->where('app_user.activo', true)` | Columna `activo`, no `role_id` |
| `Modules/User/Entities/User.php:146` | `belongsToMany(...'app_user'...)` | Sin withPivot — no accede a ninguna columna pivot |
| `Modules/Apps/Livewire/Apps/AppsIndex.php:70` | Comentario `// --- Gestión de usuarios directos (app_user) ---` | Solo comentario |
| `apps-index.blade.php:234` | Comentario `{{-- Modal gestión de usuarios directos ---}}` | Solo comentario |

### `role_id` en el código

Todas las ocurrencias de `role_id` en el repositorio corresponden a `users.role_id`
(FK del usuario a su rol), **no** a `app_user.role_id`:

| Archivo | Uso | Tabla |
|---------|-----|-------|
| `App.php:64` | `$user->role_id` en subquery de `app_role` | `users.role_id` |
| `EditarRolUser.php:18,33` | `$user->role_id` | `users.role_id` |
| `UserIndex.php:85,111` | `$this->role_id` en creación de usuario | `users.role_id` |

### Verificación de pivot

`App::user()` declara `withPivot('activo')` — `role_id` nunca fue expuesto como
atributo pivot.

**Conclusión V-001:** cero referencias activas a `app_user.role_id`. Seguro eliminar.

---

## 2. V-002 — Estado en BD previo a la migración

```
Columna:  role_id | bigint | NULLABLE: YES | DEFAULT: NULL
FK:       app_user_role_id_foreign → roles(id) ON DELETE SET NULL
Filas con role_id poblado: 0 / 0 filas totales en app_user
```

---

## 3. Migración ejecutada

**Archivo:** `Modules/Apps/database/migrations/2026_06_09_000003_drop_app_user_role_id.php`

```php
Schema::table('app_user', function (Blueprint $table) {
    $table->dropForeign('app_user_role_id_foreign');
    $table->dropColumn('role_id');
});
```

Estado en `migrate:status`: **Ran** (batch junto con `2026_06_09_000002`)

---

## 4. Esquema resultante de `app_user`

| Columna | Tipo | Nullable |
|---------|------|----------|
| id | bigint | NO |
| user_id | bigint | NO |
| app_id | bigint | NO |
| activo | tinyint | NO |
| created_at | timestamp | YES |
| updated_at | timestamp | YES |

FKs vigentes:
- `app_user_user_id_foreign → users(id) ON DELETE CASCADE`
- `app_user_app_id_foreign → apps(id) ON DELETE CASCADE`

---

## 5. Validación funcional post-migración

| Componente | Resultado |
|------------|-----------|
| `App::visiblesPara(Rector)` | ✓ 8 apps — idéntico a estado previo |
| `App::visiblesPara(Docente)` | ✓ 0 apps — idéntico (sin app_role para Docente) |
| `App::visiblesPara(Coordinador)` | ✓ 0 apps — idéntico (sin app_role para Coordinador) |
| Laravel boot / tinker | ✓ Sin errores |
| Pivot `activo` accesible | ✓ Confirmado vía `App::user()` relation |

Nota: Coordinador con 0 apps es comportamiento preexistente — no hay entradas en
`app_role` para role_id=3. No es una regresión de esta implementación.

---

## 6. Documentación actualizada

| Documento | Cambio |
|-----------|--------|
| `docs/adr/ADR-008-*.md` | DP-001 cerrada: "✅ RESUELTA — IMPL-ADR-009 (2026-06-08)" |
| `docs/changelog/apps.md` | Entrada v1.4.0 agregada |
| `docs/changelog/bhagamapps.md` | Entrada v1.6.3 agregada |
| `CHANGELOG.md` | Entrada v1.6.3 agregada |
| `VERSIONING.md` | Apps v1.4.0, BhagamApps v1.6.3 |
| `config/versiones.php` | Apps '1.4.0', BhagamApps '1.6.3' |

---

## 7. Versionado

| Componente | Versión anterior | Versión nueva |
|------------|-----------------|---------------|
| Apps | v1.3.1 | v1.4.0 |
| BhagamApps (plataforma) | v1.6.2 | v1.6.3 |

Apps recibe minor (v1.4.0) porque es un cambio de modelo de datos con impacto en
la interfaz pública del módulo (relación pivot simplificada). BhagamApps recibe
patch porque el cambio es interno y no introduce nueva funcionalidad transversal.
