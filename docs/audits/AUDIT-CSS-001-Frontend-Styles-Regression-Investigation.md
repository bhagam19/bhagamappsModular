# AUDIT-CSS-001 — Frontend Styles Regression Investigation

**Estado:** CERRADO  
**Fecha:** 2026-06-10  
**Tipo:** Investigación — NO se modificó ningún archivo de código  
**Alcance:** Commits auditados: `954f856`, `c31b10e`, `e4364d7`, `07ac094`, `9c54d40`  
**Reportado por:** Adolfo Ruiz  
**Síntoma:** Páginas internas se renderizan sin CSS — AdminLTE no aplica estilos, solo HTML plano visible

---

## Resumen Ejecutivo

La regresión de estilos **no fue causada por ningún commit de código** de los commits auditados.
El origen es una incompatibilidad de configuración servidor-aplicación preexistente:

> `APP_URL` / `ASSET_URL` apuntan a `https://bhagamapps.com/Modular`, pero el certificado SSL
> del servidor es para `vmi2865722.contaboserver.net`. Los navegadores rechazan la conexión
> HTTPS y bloquean todos los assets (CSS/JS) antes de que se descarguen.

---

## Fase 1 — Análisis de Commits Auditados

### Commits analizados

| SHA | Descripción | Archivos CSS/layout cambiados |
|-----|-------------|-------------------------------|
| `954f856` | feat(inventario): IMPL-INV-005 — Historial de Ubicaciones v2.9.0 | **Ninguno** |
| `c31b10e` | fix(inventario): IMPL-INV-004 suplemento — validaciones residuales v2.8.2 | **Ninguno** |
| `e4364d7` | fix(inventario): IMPL-INV-004 Inventory Core Remediation Package v2.8.1 | **Ninguno** |
| `07ac094` | feat(inventario): IMPL-INV-002A — Catalog & HEB Navigation Integration v2.8.0 | **Ninguno** |
| `9c54d40` | fix(inventario): IMPL-INV-003A — Responsables menu navigation integration v2.7.1 | **Ninguno** |

### Hallazgo F-001

**Ningún commit de los auditados modificó archivos de CSS, JavaScript, templates de layout,
`public/vendor/`, ni configuración de asset bundling.**

Los únicos archivos modificados en `config/adminlte.php` en esos commits son entradas del
menú lateral (`text`, `icon`, `route`, `can`). No se alteró ningún parámetro de assets.

**Conclusión Fase 1:** La regresión CSS **no es introducida por código** en ninguno de los
5 commits auditados. El origen es externo al historial de commits.

---

## Fase 2 — Configuración AdminLTE y Asset Bundling

### Archivo: `config/adminlte.php`

```php
'laravel_asset_bundling' => false,
'livewire' => false,
```

Ambos valores están en `false` **desde el commit inicial** `3f6d944` (2026-06-08) y no han
sido modificados por ningún commit posterior.

- `laravel_asset_bundling => false`: el template `master.blade.php` carga assets via `asset()` helper (caso `@default`). Correcto para la arquitectura de este proyecto.
- `livewire => false`: Livewire v3 inyecta sus propios estilos/scripts automáticamente. Correcto — `@livewireStyles`/`@livewireScripts` fueron deprecados en v3.

### Archivo: `resources/views/vendor/adminlte/master.blade.php`

El bloque `@default` genera tags `<link>` y `<script>` usando `asset()`:

```blade
@default
    {{-- AdminLTE CSS --}}
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    {{-- FontAwesome --}}
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
```

`asset()` usa `ASSET_URL` del `.env` para construir URLs absolutas.

### Hallazgo F-002

La configuración AdminLTE es correcta y no ha cambiado. El comportamiento esperado es que
`asset()` genere URLs absolutas usando `ASSET_URL`. El problema está en el valor de `ASSET_URL`.

---

## Fase 3 — Existencia Física de Assets

Verificación de presencia en disco:

```
public/vendor/adminlte/dist/css/adminlte.min.css     ✅ EXISTE
public/vendor/adminlte/dist/js/adminlte.min.js       ✅ EXISTE
public/vendor/fontawesome-free/css/all.min.css       ✅ EXISTE
public/vendor/jquery/jquery.min.js                   ✅ EXISTE
public/vendor/livewire/livewire.min.js               ✅ EXISTE
public/build/manifest.json                           ✅ EXISTE (solo para páginas auth)
```

### Hallazgo F-003

Todos los assets existen físicamente. El problema **no es** archivos faltantes ni publicación
pendiente de vendors.

---

## Fase 4 — Comportamiento en Tiempo de Ejecución

### 4.1 URLs generadas por PHP

Variables en `.env`:

```
APP_URL=https://bhagamapps.com/Modular
ASSET_URL=https://bhagamapps.com/Modular
```

Por lo tanto `asset('vendor/adminlte/dist/css/adminlte.min.css')` genera:

```
https://bhagamapps.com/Modular/vendor/adminlte/dist/css/adminlte.min.css
```

### 4.2 Test HTTPS sin bypass SSL (como lo hace un navegador)

```bash
curl -v --max-time 5 \
  "https://bhagamapps.com/Modular/vendor/adminlte/dist/css/adminlte.min.css"
```

**Resultado:**

```
* Server certificate:
*  subject: CN=vmi2865722.contaboserver.net
*  subjectAltName does not match bhagamapps.com
* SSL: no alternative certificate subject name matches target host name 'bhagamapps.com'
curl: (60) SSL: no alternative certificate subject name matches target host name 'bhagamapps.com'
```

→ **Error de certificado SSL. La conexión es bloqueada antes de recibir respuesta.**

### 4.3 Test HTTPS con bypass SSL (curl -k)

```bash
curl -sk -o /dev/null -w "%{http_code} -> %{redirect_url}\n" \
  "https://bhagamapps.com/Modular/vendor/adminlte/dist/css/adminlte.min.css"
```

**Resultado:** `301 -> http://bhagamapps.com/Modular/vendor/adminlte/dist/css/adminlte.min.css`

→ El servidor nginx hace redirect 301 de HTTPS a HTTP para todos los assets.

### 4.4 Test HTTP directo (destino del redirect)

```bash
curl -s -o /dev/null -w "%{http_code}\n" \
  "http://bhagamapps.com/Modular/vendor/fontawesome-free/css/all.min.css"
```

**Resultado:** `200` — El CSS correcto se sirve por HTTP.

### 4.5 Test de la página principal

```
https://bhagamapps.com/Modular/login  →  301 → http://bhagamapps.com/Modular/login
http://bhagamapps.com/Modular/login   →  200 OK
```

La app completa funciona vía HTTP. El servidor nginx redirige toda petición HTTPS a HTTP.

### Hallazgo F-004

La cadena de redirección funciona cuando SSL es ignorado:

```
https://asset  →  [301]  →  http://asset  →  [200]  OK
```

Pero los navegadores NO ignoran errores SSL. Al encontrar un certificado inválido para
`bhagamapps.com`, la petición HTTPS es **bloqueada por error de certificado**, no llega
a recibir el 301, y el asset nunca se descarga.

---

## Fase 5 — Identificación de Causas Raíz

### RC-001 — CAUSA RAÍZ PRIMARIA: Certificado SSL inválido para `bhagamapps.com`

| Campo | Valor |
|-------|-------|
| Certificado instalado | `CN=vmi2865722.contaboserver.net` |
| Host solicitado | `bhagamapps.com` |
| SubjectAltName match | ❌ NO COINCIDE |
| Error reportado | `SSL: no alternative certificate subject name matches target host name 'bhagamapps.com'` |

**Impacto directo:**

1. `ASSET_URL` = `https://bhagamapps.com/Modular` → `asset()` genera URLs HTTPS
2. Navegador intenta cargar `https://bhagamapps.com/Modular/vendor/adminlte/...`
3. Certificado no es válido para `bhagamapps.com` → **SSL error**
4. Navegador bloquea la petición → el CSS nunca llega al DOM
5. Página se renderiza como HTML plano sin estilos

---

### RC-002 — CAUSA RAÍZ SECUNDARIA: nginx fuerza HTTP pero APP_URL/ASSET_URL usan HTTPS

| Dato | Valor |
|------|-------|
| Comportamiento nginx | Redirige HTTPS → HTTP (301) para todas las rutas |
| Razón probable | Certificado SSL no válido para el dominio → workaround forzado |
| `APP_URL` en `.env` | `https://bhagamapps.com/Modular` |
| `ASSET_URL` en `.env` | `https://bhagamapps.com/Modular` |

**Inconsistencia detectada:**

El servidor sirve contenido por HTTP pero la aplicación genera URLs en HTTPS. Esto crea un
desajuste permanente: los assets tienen URLs que el servidor no puede servir correctamente
sobre TLS.

Adicionalmente, en la respuesta HTTP del login se observan dos esquemas distintos en la misma
página:
- Vite assets: `https://bhagamapps.com/Modular/build/assets/app-BHzvmMD8.css` (HTTPS, via `ASSET_URL`)
- Livewire script: `http://bhagamapps.com/Modular/vendor/livewire/livewire.min.js` (HTTP, generado desde la URL de la petición entrante)

---

### RC-003 — BUG INDEPENDIENTE: `Mantenimiento::bienes()` FK incorrecta

Archivo: `Modules/Inventario/Entities/Mantenimiento.php`

```php
// ACTUAL — INCORRECTO
protected $fillable = ['nom_mantenimiento'];  // debería ser 'nombre'
public function bienes() {
    return $this->hasMany(Bien::class, 'cod_mantenimiento');  // debería ser 'mantenimiento_id'
}
```

**Impacto:** Genera `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'bienes.cod_mantenimiento'`
cuando se accede a la página del catálogo de Mantenimientos. Produce error 500 en producción.

Este bug no es la causa de la regresión CSS pero debe corregirse como bug separado.

---

### RC-004 — CONTEXTUAL: Sin HSTS — Los redirects HTTP sí funcionan por curl

No se detectó cabecera `Strict-Transport-Security` en las respuestas del servidor. Esto
significa que HSTS preload no es un factor adicional. El problema se limita a la validación
del certificado SSL en la carga inicial del asset HTTPS.

---

## Resumen de Causas

| ID | Tipo | Descripción | Impacto |
|----|------|-------------|---------|
| RC-001 | **PRIMARIA** | Certificado SSL `vmi2865722.contaboserver.net` no cubre `bhagamapps.com` | CSS/JS bloqueados por SSL error en navegadores |
| RC-002 | **SECUNDARIA** | nginx fuerza HTTP; `APP_URL`/`ASSET_URL` usan HTTPS | URLs de assets apuntan a endpoint con SSL inválido |
| RC-003 | **BUG SEPARADO** | `Mantenimiento::bienes()` FK incorrecta | HTTP 500 en catálogo de Mantenimientos |

---

## ¿Cuándo se rompió?

No hay evidencia en git de cuándo exactamente empezó la regresión. Los últimos 5 commits
auditados no introdujeron el problema. Las posibles explicaciones:

1. **Siempre estuvo presente** pero el error solo se hizo visible cuando se empezaron a probar
   las páginas AdminLTE con mayor frecuencia (tras IMPL-INV-002A en adelante).
2. **Cambio de servidor/dominio**: Si el proyecto fue movido al dominio `bhagamapps.com` sin
   provisionar un certificado SSL para ese dominio, el problema data de ese momento.
3. **Cambio de `APP_URL`/`ASSET_URL`**: Si estos valores fueron cambiados de HTTP a HTTPS en
   `.env` sin el certificado correspondiente, el problema data de ese cambio (no rastreable en
   git porque `.env` no está versionado).

---

## Correcciones Propuestas (sin aplicar)

> **RESTRICCIÓN:** Este documento es solo investigación. Las correcciones NO han sido aplicadas.

### Opción A — Corregir el servidor (recomendada a largo plazo)

1. Obtener un certificado SSL válido para `bhagamapps.com` (Let's Encrypt es gratuito).
2. Instalar y configurar el certificado en nginx para el VirtualHost `bhagamapps.com`.
3. Verificar que `APP_URL` = `ASSET_URL` = `https://bhagamapps.com/Modular` es correcto.

### Opción B — Corregir la configuración de la aplicación (solución inmediata)

Cambiar en `.env`:

```
APP_URL=http://bhagamapps.com/Modular
ASSET_URL=http://bhagamapps.com/Modular
```

Esto alinea la generación de URLs con el protocolo que el servidor realmente sirve. Los assets
se solicitarán por HTTP directamente y se recibirán con 200 OK sin pasar por el SSL inválido.

**Trade-off**: El sitio operará sobre HTTP sin cifrado hasta que se corrija el certificado.

### Opción C — Corrección del bug RC-003 (independiente)

En `Modules/Inventario/Entities/Mantenimiento.php`:

```php
// Cambiar:
protected $fillable = ['nom_mantenimiento'];
public function bienes() {
    return $this->hasMany(Bien::class, 'cod_mantenimiento');
}

// Por:
protected $fillable = ['nombre'];
public function bienes() {
    return $this->hasMany(Bien::class, 'mantenimiento_id');
}
```

---

## Evidencia Técnica Completa

### Evidencia 1 — Error SSL literal

```
curl -v https://bhagamapps.com/Modular/vendor/adminlte/dist/css/adminlte.min.css

* Server certificate:
*   subject: CN=vmi2865722.contaboserver.net
*   subjectAltName does not match bhagamapps.com
* SSL: no alternative certificate subject name matches target host name 'bhagamapps.com'
curl: (60) SSL: no alternative certificate subject name matches target host name 'bhagamapps.com'
```

### Evidencia 2 — Redirect HTTPS→HTTP (con bypass SSL)

```
curl -sk -o /dev/null -w "%{http_code} -> %{redirect_url}\n" \
  https://bhagamapps.com/Modular/vendor/adminlte/dist/css/adminlte.min.css
→ 301 -> http://bhagamapps.com/Modular/vendor/adminlte/dist/css/adminlte.min.css

curl -sk -o /dev/null -w "%{http_code} -> %{redirect_url}\n" \
  https://bhagamapps.com/Modular/vendor/fontawesome-free/css/all.min.css
→ 301 -> http://bhagamapps.com/Modular/vendor/fontawesome-free/css/all.min.css

curl -sk -o /dev/null -w "%{http_code} -> %{redirect_url}\n" \
  https://bhagamapps.com/Modular/vendor/adminlte/dist/js/adminlte.min.js
→ 301 -> http://bhagamapps.com/Modular/vendor/adminlte/dist/js/adminlte.min.js

curl -sk -o /dev/null -w "%{http_code} -> %{redirect_url}\n" \
  https://bhagamapps.com/Modular/vendor/jquery/jquery.min.js
→ 301 -> http://bhagamapps.com/Modular/vendor/jquery/jquery.min.js
```

### Evidencia 3 — HTTP assets sirven correctamente

```
curl -s -o /dev/null -w "%{http_code}\n" \
  http://bhagamapps.com/Modular/vendor/fontawesome-free/css/all.min.css
→ 200 OK (Font Awesome CSS content confirmed)
```

### Evidencia 4 — Página completa también redirige HTTPS→HTTP

```
https://bhagamapps.com/Modular/login  →  301 → http://bhagamapps.com/Modular/login
http://bhagamapps.com/Modular/login   →  200 OK
```

### Evidencia 5 — `.env` con HTTPS

```
APP_URL=https://bhagamapps.com/Modular
ASSET_URL=https://bhagamapps.com/Modular
```

### Evidencia 6 — Commits auditados sin cambios CSS

```
954f856 — 22 files changed, 930 insertions(+), 5 deletions(-)  — solo PHP/Blade/Migrations
c31b10e — 10 files changed, 139 insertions(+), 41 deletions(-) — solo PHP/Blade
e4364d7 — 11 files changed, 333 insertions(+), 106 deletions(-) — solo PHP/Blade
07ac094 —  8 files changed, 204 insertions(+), 4 deletions(-)  — solo PHP/Blade + adminlte.php (sidebar items)
9c54d40 —  7 files changed, 101 insertions(+), 4 deletions(-)  — solo PHP/Blade + adminlte.php (sidebar items)
```

### Evidencia 7 — Assets físicamente presentes

```
public/vendor/adminlte/dist/css/adminlte.min.css    ✅
public/vendor/adminlte/dist/js/adminlte.min.js      ✅
public/vendor/fontawesome-free/css/all.min.css      ✅
public/vendor/jquery/jquery.min.js                  ✅
public/vendor/livewire/livewire.min.js              ✅
public/build/manifest.json                          ✅
```

---

## Conclusiones

1. La regresión CSS **no fue introducida por código** — ningún commit reciente cambió assets, layouts ni configuración de bundling.
2. La causa raíz es **el certificado SSL del servidor** (`vmi2865722.contaboserver.net`) que no cubre el dominio `bhagamapps.com`, combinado con que `ASSET_URL` genera URLs HTTPS que los navegadores no pueden verificar.
3. Los assets existen, son accesibles por HTTP, y los archivos físicos están completos.
4. Existe un bug separado (`RC-003`) en `Mantenimiento::bienes()` que causa HTTP 500 independiente de la regresión CSS.
5. La corrección inmediata disponible es cambiar `APP_URL`/`ASSET_URL` a HTTP en `.env` — sin tocar código.

---

*Auditoría realizada por: Claude Sonnet 4.6 — BhagamApps Engineering*
