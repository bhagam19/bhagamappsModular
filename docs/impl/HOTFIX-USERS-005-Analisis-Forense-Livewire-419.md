# HOTFIX-USERS-005 — Análisis Forense Livewire 419

**Fecha:** 2026-06-11  
**Módulo:** User (`Modules/User`)  
**Severidad:** CRÍTICA  
**Estado:** PENDIENTE ACCIÓN SERVIDOR — requiere corrección en configuración PHP-FPM

---

## Resumen ejecutivo

El Error 419 en búsqueda/filtros/ordenamiento de `/iee/users/users` **no es sesión expirada**.

La causa raíz es un **directorio temporal del servidor sin permisos de escritura** para el proceso PHP-FPM. Cuando el cuerpo de una petición POST supera 16,383 bytes, PHP necesita escribir el desbordamiento en un archivo temporal. Al fallar la escritura, PHP **descarta todo el cuerpo de la petición**, incluido el token CSRF. Laravel entonces falla la verificación CSRF y devuelve HTTP 419.

---

## Evidencia forense

### FW-001 — Descarte de cuerpo POST por PHP

Apache error log (`/var/log/apache2/domains/bhagamapps.com.error.log`):

```
[proxy_fcgi:error] AH01071: Got error '
  PHP Notice:  PHP Request Startup: file created in the system's temporary directory
  PHP Warning: PHP Request Startup: Unable to create temporary file, Check permissions in temporary files directory.
  PHP Warning: PHP Request Startup: POST data can't be buffered; all data discarded
'
```

### FW-002 — Umbral exacto: 16,383 bytes

Pruebas con peticiones de tamaños crecientes directamente a Apache (puerto 8080):

| Tamaño cuerpo | `strlen(php://input)` | Resultado |
|---|---|---|
| 16,380 bytes | 16,380 | ✓ OK |
| 16,383 bytes | 16,383 | ✓ OK |
| 16,384 bytes | **0** | ✗ FALLO |
| 16,385+ bytes | **0** | ✗ FALLO |

El umbral es exactamente **16,384 = 16 × 1024 = 16 KB**, que es el tamaño del buffer interno de lectura POST de PHP (`SAPI_POST_BLOCK_SIZE`). Cuando el cuerpo supera este buffer, PHP necesita volcar el exceso a un archivo temporal.

### FW-003 — Directorio temporal inaccesible

```
/home/adolfo/tmp
  Owner:        root:root
  Permissions:  drwxrwx--x (0771)
  Adolfo access: --x (solo traversal, SIN escritura)
```

El pool PHP-FPM `bhagamapps.com.conf` configura:
```ini
user = adolfo
php_admin_value[upload_tmp_dir] = /home/adolfo/tmp
```

El proceso PHP-FPM corre como usuario `adolfo`. La carpeta `/home/adolfo/tmp` está propiedad de `root:root` con permisos `0771`, por lo que `adolfo` solo tiene permiso de ejecución (`--x`), sin escritura. `tempnam('/home/adolfo/tmp', 'x')` retorna `false`.

### FW-004 — Cadena causal completa

1. Petición POST con cuerpo ≥ 16,384 bytes llega a PHP-FPM
2. PHP lee los primeros 16,383 bytes en buffer interno
3. PHP intenta crear archivo temporal en `/home/adolfo/tmp` para el resto
4. `tempnam('/home/adolfo/tmp', ...)` → `false` (sin permisos)
5. PHP descarta **todo** el cuerpo POST (`POST data can't be buffered; all data discarded`)
6. `php://input` retorna cadena vacía
7. `$request->getContent()` → `""`
8. `json_decode("")` → `null`
9. `$request->input('_token')` → `null`
10. `VerifyCsrfToken::tokensMatch()` → `is_string(null)` → `false`
11. `TokenMismatchException` → HTTP 419

### FW-005 — Por qué ocurre SOLO en Users y SOLO tras IMPL-USERS-002

El snapshot Livewire de `UserIndex` mide **15,735 bytes** (325 componentes hijo en `memo.children`, estado de búsqueda/filtros/ordenamiento añadidos en IMPL-USERS-002).

El cuerpo total de la petición Livewire es **~17,967 bytes** (snapshot + envoltura JSON), que supera el límite de 16,383 bytes.

Antes de IMPL-USERS-002, el `UserIndex` era más simple: sin `rolesDisponibles`, `columnasVisibles`, `sortBy`, `sortDir`, `estadoFiltro`, `roleFiltro`. El snapshot era más pequeño y el cuerpo quedaba bajo 16,383 bytes. Tras la implementación, el snapshot creció 1,584 bytes por encima del umbral.

Otros componentes Livewire de la plataforma (Inventario, Apps, etc.) tienen snapshots más pequeños y no superan el límite.

### FW-006 — Verificaciones adicionales

- CSRF token correcto: `4nwUrM1WQVXzIYkXPqYBtHnpgpNKT9iEN7LZ8YJ5` ✓
- Sesión válida, no expirada (20 min de actividad) ✓
- Checksum Livewire válido (verificado via tinker) ✓
- `PersistentMiddleware` solo re-ejecuta `Authenticate` + `SubstituteBindings` ✓
- `post_max_size = 256M`, `upload_max_filesize = 256M` ✓ (no son el problema)
- `/tmp` es accesible y escribible por `adolfo` ✓

---

## Causa raíz

**Directorio `/home/adolfo/tmp` tiene permisos incorrectos** (`root:root 0771`). El proceso PHP-FPM (`user=adolfo`) no puede crear archivos temporales allí. PHP descarta el cuerpo completo de peticiones POST ≥ 16 KB, causando que el token CSRF sea invisible para Laravel.

**Esto NO es un bug del código de la aplicación.** Es una misconfiguration del servidor, posiblemente introducida por una actualización o reconstrucción de HestiaCP (change time: 2026-05-30 04:42:06).

---

## Fix requerido (acción del servidor)

### Opción A — Corregir permisos del directorio temporal (RECOMENDADO)

```bash
sudo chown adolfo:adolfo /home/adolfo/tmp
sudo chmod 755 /home/adolfo/tmp
```

Esto restaura el propietario correcto. HestiaCP asigna `/home/adolfo/tmp` al pool PHP-FPM del usuario, por lo que el directorio debería pertenecer a `adolfo`.

### Opción B — Cambiar upload_tmp_dir al pool PHP-FPM

```bash
sudo sed -i \
  's|php_admin_value\[upload_tmp_dir\] = /home/adolfo/tmp|php_admin_value[upload_tmp_dir] = /tmp|' \
  /etc/php/8.3/fpm/pool.d/bhagamapps.com.conf

sudo systemctl restart php8.3-fpm
```

### Verificación post-fix

```bash
# Debe retornar HTTP 200
curl -s -o /dev/null -w "%{http_code}\n" \
  -X POST "http://bhagamapps.com/iee/livewire/update" \
  -H "Content-Type: application/json" \
  -H "X-Livewire: true" \
  --data-binary @/tmp/real_body.json \
  -b "iee_session=<cookie_válida>"
```

Validaciones funcionales (V-001 a V-006):
- [ ] V-001: Búsqueda por nombre funciona sin 419
- [ ] V-002: Filtro por rol funciona sin 419
- [ ] V-003: Ordenamiento por columna funciona sin 419
- [ ] V-004: No se produce 419 en ninguna acción reactiva
- [ ] V-005: No se requiere refrescar la página
- [ ] V-006: Comportamiento consistente con otros componentes Livewire

---

## Cambios de código aplicados

**Ninguno.** La causa raíz es configuración de servidor. No se requieren cambios en el código de la aplicación.

Los cambios de código de HOTFIX-USERS-004 (`UserIndex::render()` sin `->layout()`, `CheckForzarCambioPassword` con `'livewire'` en lugar de `'livewire/'`) permanecen válidos y correctos, aunque no sean la causa del 419.

---

## Artefactos de diagnóstico eliminados

- `public/opcache_reset.php` (temporal)
- `vendor/.../VerifyCsrfToken.php` — debug temporal revertido
- `app/Exceptions/Handler.php` — debug temporal revertido
- `/tmp/csrf_debug_*.txt`, `/tmp/*_body.json` — archivos de prueba eliminados
