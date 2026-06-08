# IMPL-002 — Security Hardening (Fase 1)

**Fecha:** 2026-06-08
**Referencia:** Auditoría Integral BhagamAppsModular — Hallazgos de Seguridad P1
**Estado:** Completado (con un ítem pendiente de acción en servidor)

---

## Fix 1 — Restricción del registro público

### Hallazgo original

El formulario de registro (`register.blade.php`) mostraba un `<select>` con las
opciones "Docente" y "Estudiante" pero enviaba los valores `2` y `3`. En la tabla
`roles`, `id=2` es **Rector** y `id=3` es **Coordinador**. Todo usuario registrado
públicamente obtenía acceso privilegiado sin saberlo.

Adicionalmente, `CreateNewUser.php` validaba `'role_id' => ['required', 'integer', 'in:2,3']`,
permitiendo explícitamente solo Rector y Coordinador.

### Decisión de diseño

El registro público se mantiene habilitado pero queda restringido a roles de bajo
privilegio: **Docente** (`id=5`) y **Estudiante** (`id=6`). Los roles privilegiados
(Administrador, Rector, Coordinador, Auxiliar) solo pueden asignarse desde el panel
de gestión de usuarios por un administrador autenticado.

### Archivos modificados

**`resources/views/auth/register.blade.php`**
- Corregidos valores del select: `2` → `5` (Docente), `3` → `6` (Estudiante)
- Corregida comparación `old()`: ahora compara contra el valor numérico correcto

**`app/Actions/Fortify/CreateNewUser.php`**
- Validación cambiada de `in:2,3` a `in:5,6`

### Impacto

Ningún usuario registrado públicamente podrá obtener roles privilegiados. Los
116 usuarios existentes con roles asignados previamente no se ven afectados.

---

## Fix 2 — Registro y aplicación del middleware CheckPermission

### Hallazgo original

El middleware `CheckPermission` existía en `app/Http/Middleware/CheckPermission.php`
y tenía alias `'permission'` en `app/Http/Kernel.php`, pero:

1. En Laravel 11, el mecanismo de middleware migró a `bootstrap/app.php`. El alias
   del Kernel podía no estar disponible en el nuevo pipeline.
2. El middleware nunca era aplicado a ninguna ruta de módulo.

### Archivos modificados

**`bootstrap/app.php`**
- Registrado el alias `permission` en el pipeline de Laravel 11:
  ```php
  $middleware->alias([
      'permission' => \App\Http\Middleware\CheckPermission::class,
  ]);
  ```

**`Modules/Inventario/routes/web.php`**
- `GET /inventario/bienes` → `permission:ver-bienes`
- `GET /inventario/actas` → `permission:ver-actas-de-entrega`
- `GET /inventario/actas/{userId}/pdf` → `permission:ver-actas-de-entrega`
- `GET /inventario/hmb` → `permission:gestionar-historial-modificaciones-bienes`
- `GET /inventario/heb` → `permission:gestionar-historial-eliminaciones-bienes`

**`Modules/User/Routes/web.php`**
- `resource users` → `permission:ver-usuarios`
- `resource roles` → `permission:ver-roles`
- `resource permissions` → `permission:ver-permisos`

### Decisión de diseño

El middleware se aplica a los métodos `index` (listado) como primera línea de
defensa. Los Livewire components mantienen sus propias verificaciones en `mount()`
como segunda capa. Esta es una estrategia de defensa en profundidad:
- La **ruta** verifica si el usuario puede acceder a la sección
- El **componente** verifica si puede ejecutar cada acción específica (crear, editar, eliminar)

Los métodos no-index del resource (store, update, destroy) en los controllers
existen como shell de Livewire y no exponen funcionalidad directamente; la
autorización granular de esas acciones vive en los componentes Livewire.

### Impacto

Usuarios sin el permiso correspondiente reciben HTTP 403 al intentar acceder
a rutas de módulos, sin necesidad de cargar el componente Livewire.

---

## Fix 3 — Rate limiting para registro

### Hallazgo original

El login ya tenía rate limiting (`Limit::perMinute(5)`) configurado en
`FortifyServiceProvider`, pero el endpoint de registro no tenía límite.
Un atacante podía crear cuentas masivamente.

### Archivos modificados

**`app/Providers/FortifyServiceProvider.php`**
- Agregado limiter `register`: 3 intentos por minuto por IP

**`config/fortify.php`**
- Agregado `'register' => 'register'` al array `limiters`

### Configuración aplicada

| Endpoint | Límite | Clave |
|---|---|---|
| `/login` | 5 req/min | `email + IP` |
| `/register` | 3 req/min | `IP` |
| `/two-factor-challenge` | 5 req/min | `session login.id` |

---

## Fix 4 — Configuración HTTPS

### Estado actual

| Parámetro | Valor actual | Estado |
|---|---|---|
| `APP_URL` | `https://bhagamapps.com/Modular` | ✅ Correcto para cuando SSL esté activo |
| `SESSION_SECURE_COOKIE` | No configurado (default: false) | ⚠️ Pendiente activación |
| SSL en servidor | No habilitado (HestiaCP) | ❌ Acción en servidor requerida |

### Decisión de diseño

`SESSION_SECURE_COOKIE=true` NO se activó en esta fase porque el servidor aún
sirve tráfico HTTP. Activarlo antes de habilitar SSL en HestiaCP rompería todas
las sesiones (el browser no enviará cookies `Secure` por HTTP).

### Archivo modificado

**`.env`**
- Agregado comentario documentando la acción pendiente:
  ```ini
  # PENDIENTE: activar cuando SSL esté habilitado en HestiaCP
  # SESSION_SECURE_COOKIE=true
  ```

### Acción pendiente en servidor (obligatoria)

Una vez habilitado SSL en HestiaCP (ver IMPL-SSL pendiente):
1. Descomentar `SESSION_SECURE_COOKIE=true` en `.env`
2. Ejecutar `php artisan config:clear`
3. Verificar que las cookies lleguen con flag `Secure` en las DevTools

---

## Fix 5 — Análisis de duplicados en permission_role

### Hallazgo

La tabla `permission_role` contiene **156 registros** pero solo **80 son únicos**.
Hay **76 registros duplicados** — exactamente 2 entradas por cada par (role_id, permission_id).

**Causa raíz:** El seeder `Permission_RoleSeeder` fue ejecutado dos veces en la
instancia de producción (o el seeder no tiene protección `firstOrCreate`).

### Distribución de duplicados por rol

| Rol | Permisos únicos | Registros reales | Duplicados |
|---|---|---|---|
| Administrador | 26 | 52 | 26 |
| Rector | 26 | 52 | 26 |
| Coordinador | 8 | 16 | 8 |
| Auxiliar | 8 | 16 | 8 |
| Docente | 8 | 16 | 8 |
| **Total** | **76** | **152** (+4 sin dup.) | **76** |

### Plan de migración (no ejecutado en esta fase)

La limpieza requiere una migración en dos pasos para garantizar integridad:

**Paso A — Crear migración de limpieza:**
```php
// database/migrations/FECHA_clean_permission_role_duplicates.php
DB::statement('
    DELETE pr1 FROM permission_role pr1
    INNER JOIN permission_role pr2
    WHERE pr1.id > pr2.id
      AND pr1.role_id = pr2.role_id
      AND pr1.permission_id = pr2.permission_id
');
```

**Paso B — Agregar constraint UNIQUE:**
```php
Schema::table('permission_role', function (Blueprint $table) {
    $table->unique(['role_id', 'permission_id'], 'permission_role_unique');
});
```

**Impacto funcional actual:** Ninguno. El método `hasPermission()` usa
`->where('slug', $slug)->exists()` que retorna `true` con cualquier cantidad
de registros. Los duplicados son inofensivos funcionalmente pero consumen
espacio y complican auditorías futuras.

**Prioridad de ejecución:** Incluir en Fase 2 (Estabilidad de datos).

---

## Archivos modificados

| Archivo | Tipo de cambio |
|---|---|
| `resources/views/auth/register.blade.php` | Valores del select corregidos |
| `app/Actions/Fortify/CreateNewUser.php` | Validación `in:5,6` |
| `bootstrap/app.php` | Alias `permission` registrado |
| `Modules/Inventario/routes/web.php` | Middleware `permission:*` en rutas clave |
| `Modules/User/Routes/web.php` | Middleware `permission:*` en recursos |
| `app/Providers/FortifyServiceProvider.php` | Rate limiter `register` añadido |
| `config/fortify.php` | Limiter `register` configurado |
| `.env` | Comentario SESSION_SECURE_COOKIE añadido |

**Total archivos modificados:** 8

---

## Pendiente (fuera de alcance de esta fase)

- Habilitar SSL en HestiaCP y activar `SESSION_SECURE_COOKIE=true` — acción en servidor
- Ejecutar migración de limpieza de `permission_role` — Fase 2
- Middleware `permission` en métodos granulares de resources (store/update/destroy) — Fase 3
