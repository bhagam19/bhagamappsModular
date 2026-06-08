# AUDIT-006 — Production Configuration and Infrastructure Readiness

**Estado:** BORRADOR
**Aprobación requerida:** Dirección General
**Fecha de auditoría:** 2026-06-08
**Responsable:** Auditoría

> Este documento NO debe considerarse aprobado ni formar parte del baseline hasta
> recibir autorización expresa de Dirección General.

---

# Resumen Ejecutivo

Se auditó el estado real de preparación de producción de BhagamAppsModular,
evaluando todos los riesgos identificados en BASELINE-001 y el estado actual de
los IMPL pendientes IMPL-005 e IMPL-006.

## Hallazgos críticos

Dos hallazgos de alto riesgo fueron identificados que no figuraban en BASELINE-001
con la gravedad que merecen:

1. **H-002 — HTTPS no operativo para bhagamapps.com**: La infraestructura no tiene
   SSL/HTTPS configurado para el dominio. El servidor redirige activamente de
   HTTPS a HTTP. BASELINE-001 documentó incorrectamente que el sitio corría
   bajo HTTPS.

2. **H-003 — Archivos de diagnóstico ejecutables en `public/`**: Tres archivos de
   diagnóstico accesibles públicamente, uno de los cuales ejecuta comandos de
   shell vía `proc_open`.

## Resultado

**Riesgo general: ALTO**

Se requiere PLAN-IMPL con acciones inmediatas para H-002 y H-003.

---

# 1. Configuración Laravel

## Estado auditado

| Variable | Valor actual | Recomendado | Estado |
|----------|-------------|-------------|--------|
| `APP_ENV` | `production` | `production` | ✅ |
| `APP_DEBUG` | `false` | `false` | ✅ |
| `APP_KEY` | Configurado | Configurado | ✅ |
| `APP_URL` | `https://bhagamapps.com/Modular` | — | ⚠️ Ver H-002 |
| `APP_TIMEZONE` | `America/Bogota` | — | ✅ |
| `SESSION_DRIVER` | `database` | `database` | ✅ |
| `SESSION_LIFETIME` | `120` | `120` | ✅ |
| `SESSION_SECURE_COOKIE` | *comentado* (= `null`) | `true` | ⚠️ Ver H-004 |
| `SESSION_DOMAIN` | `bhagamapps.com` | `bhagamapps.com` | ✅ |
| `SESSION_PATH` | `/Modular` | `/Modular` | ✅ |
| `CACHE_STORE` | `file` | `file` (aceptable) | ✅ |
| `QUEUE_CONNECTION` | `sync` | `sync` (aceptable) | ✅ |
| `LOG_CHANNEL` | `stack` | `stack` | ✅ |
| `LOG_LEVEL` | `error` | `error` | ✅ |
| `MAIL_MAILER` | `log` | SMTP real | ❌ Ver H-005 |
| `BCRYPT_ROUNDS` | `12` | `12` | ✅ |

### Observaciones

**SESSION_SECURE_COOKIE:** La línea `# SESSION_SECURE_COOKIE=true` está presente en
`.env` pero comentada. Laravel resuelve la variable como `null`, no como `true`.
El comportamiento es idéntico a ausencia total del flag. Pendiente de IMPL-005.

**CACHE_STORE=file:** Aceptable para la carga actual. Sin Redis ni Memcached
configurados. No es un riesgo inmediato dado el volumen de usuarios (116).

**QUEUE_CONNECTION=sync:** Los jobs se ejecutan síncronos. Aceptable ya que no
hay jobs configurados actualmente (`php artisan schedule:list` → 0 tareas).

### Trazabilidad

- BASELINE-001: Sí (§ Configuración de producción)
- IMPL relacionado: IMPL-005, IMPL-006
- ADR afectado: Ninguno
- ROADMAP-001: Impacta (Fase 1 — Gobierno y estabilización)
- PMP-001: Impacta (OE-04 Fortalecer seguridad)

---

# 2. Seguridad HTTPS

## Estado auditado

| Componente | Estado | Detalle |
|-----------|--------|---------|
| SSL para bhagamapps.com | ❌ NO configurado | Ver H-002 |
| Certificado de dominio | ❌ Ausente | No existe `bhagamapps.com.conf` con SSL en nginx |
| Certificado del servidor | ⚠️ Let's Encrypt | CN=`vmi2865722.contaboserver.net`, válido hasta 2026-07-18 |
| Puerto 443 behavior | ❌ REDIRIGE A HTTP | Default server: `return 301 http://$host$request_uri` |
| Redirección HTTP → HTTPS | ❌ Ausente | HTTP 301 → trailing slash (mismo protocolo) |
| HSTS | ❌ Ausente | No hay headers Strict-Transport-Security |

### Verificación realizada

```bash
# HTTP → HTTP (solo slash trailing, no HTTPS redirect)
curl -sI http://bhagamapps.com/Modular
HTTP/1.1 301 Moved Permanently
Location: http://bhagamapps.com/Modular/   ← HTTP, no HTTPS

# Certificado en puerto 443
openssl s_client -connect bhagamapps.com:443 -servername bhagamapps.com
CN = vmi2865722.contaboserver.net   ← hostname del servidor, no del dominio
notAfter=Jul 18 07:03:49 2026 GMT
Issuer: Let's Encrypt, R13

# Comportamiento del default HTTPS server (nginx)
/etc/nginx/conf.d/147.93.176.252.conf:
  server {
    listen 147.93.176.252:443 default_server ssl;
    return 301 http://$host$request_uri;   ← HTTPS → HTTP redirect
  }
```

### Inconsistencia con BASELINE-001

BASELINE-001 (§ Configuración de producción) documenta:
> *"El sitio corre bajo HTTPS pero las cookies de sesión no tienen el flag Secure."*

Esta afirmación es **incorrecta**. La auditoría confirma que HTTPS no está
operativo para `bhagamapps.com`. El dominio sirve tráfico exclusivamente por HTTP.
El certificado disponible en puerto 443 corresponde al hostname del servidor
(`vmi2865722.contaboserver.net`), no al dominio de la aplicación.

La documentación previa — incluyendo IMPL-002 — sí era correcta al indicar que
SSL estaba pendiente. La inconsistencia está en BASELINE-001.

### Trazabilidad

- BASELINE-001: Sí — con discrepancia (ver arriba)
- IMPL relacionado: IMPL-005 (prerequisito SSL para SESSION_SECURE_COOKIE)
- ADR afectado: Ninguno
- ROADMAP-001: Impacta (Fase 1 — Riesgos críticos)
- PMP-001: Impacta (OE-04 Seguridad)

---

# 3. Cookies y Sesiones

## Estado auditado

| Parámetro | Valor actual | Estado |
|-----------|-------------|--------|
| `SESSION_DRIVER` | `database` | ✅ |
| `SESSION_SECURE_COOKIE` | `null` (comentado) | ❌ |
| `SESSION_SAME_SITE` | `lax` | ✅ |
| `SESSION_HTTP_ONLY` | `true` | ✅ |
| `SESSION_ENCRYPT` | `false` | ⚠️ Aceptable |
| Sesiones activas en BD | 19 | ✅ |

### Verificación artisan

```
php artisan config:show session
  driver ...................................... database
  secure ...................................... null       ← sin flag Secure
  http_only ................................... true       ← correcto
  same_site ................................... lax        ← correcto
  domain ...................................... bhagamapps.com
```

### Estado de IMPL-005

**Estado: PENDIENTE**

La línea `# SESSION_SECURE_COOKIE=true` está comentada en `.env`. Dado que
HTTPS no está activo para el dominio (H-002), habilitar `SESSION_SECURE_COOKIE=true`
sin SSL activo impediría que las sesiones funcionen. IMPL-005 tiene como
prerequisito la resolución de H-002.

**Secuencia requerida:**
```
Habilitar SSL en HestiaCP para bhagamapps.com
↓
Verificar HTTPS funcional
↓
Descomentar SESSION_SECURE_COOKIE=true en .env
↓
php artisan config:clear
```

### Trazabilidad

- BASELINE-001: Sí
- IMPL relacionado: IMPL-005
- ADR afectado: Ninguno
- ROADMAP-001: Impacta (Fase 1)
- PMP-001: Impacta (OE-04)

---

# 4. Correo Electrónico

## Estado auditado

| Variable | Valor actual | Estado |
|----------|-------------|--------|
| `MAIL_MAILER` | `log` | ❌ |
| `MAIL_HOST` | *(no configurado)* | ❌ |
| `MAIL_PORT` | *(no configurado)* | ❌ |
| `MAIL_USERNAME` | *(no configurado)* | ❌ |
| `MAIL_ENCRYPTION` | *(no configurado)* | ❌ |
| `MAIL_FROM_ADDRESS` | `noreply@bhagamapps.com` | ⚠️ Configurado |
| `MAIL_FROM_NAME` | `BhagamApps Modular` | ✅ |

### Verificación artisan

```
php artisan config:show mail
  default ......................... log
  mailers.smtp.host .............. smtp.mailgun.org  ← valor por defecto, no configurado
  mailers.smtp.username .......... null
  mailers.smtp.password .......... null
```

### Estado de IMPL-006

**Estado: PENDIENTE**

Con `MAIL_MAILER=log`:
- Todos los intentos de envío de email se registran en `laravel.log`.
- 116 usuarios no pueden recuperar su contraseña mediante el flujo estándar
  de Fortify.
- Las notificaciones del sistema (si se implementan) no llegarán.

No se enviaron correos de prueba durante esta auditoría (instrucción: solo
verificar configuración).

### Trazabilidad

- BASELINE-001: Sí (MAIL_MAILER = log, ❌)
- IMPL relacionado: IMPL-006
- ADR afectado: Ninguno
- ROADMAP-001: Impacta (Fase 1 — riesgos críticos)
- PMP-001: Impacta (OE-01 operación plena)

---

# 5. Infraestructura del Servidor

## Sistema Operativo y Runtime

| Componente | Versión | Estado |
|-----------|---------|--------|
| OS | Ubuntu 24.04.3 LTS (Noble Numbat) | ✅ LTS vigente |
| Kernel | Linux 6.8.0-87-generic | ✅ |
| PHP CLI | 8.4.14 | ✅ |
| **PHP-FPM (web)** | **8.3.27** | ⚠️ Ver H-006 |
| Composer | 2.9.7 | ✅ |
| Laravel | 11.44.7 | ✅ |
| MariaDB | 11.4.8-MariaDB | ✅ LTS vigente |
| HestiaCP | Instalado | ✅ |
| Nginx | Instalado (proxy) | ✅ |
| Apache2 | 8080 (backend) | ✅ |

## Recursos

| Recurso | Total | Usado | Libre | Estado |
|---------|-------|-------|-------|--------|
| Disco (`/dev/sda1`) | 242 GB | 197 GB | 45 GB | ⚠️ 82% — Ver H-007 |
| RAM | 47 GB | 7.4 GB | ~39 GB | ✅ |
| Swap | 0 B | — | — | ⚠️ Sin swap |
| Sesiones DB | — | 19 activas | — | ✅ |

## Base de Datos

| Tabla | Filas | Estado |
|-------|-------|--------|
| bienes | 1,420 | ✅ Carga IMPL-007 exitosa |
| users | 116 | ✅ |
| sessions | 19 | ✅ |
| failed_jobs | 0 | ✅ |
| permission_role | 80 | ✅ Post IMPL-003 |

### Trazabilidad

- BASELINE-001: Sí
- IMPL relacionado: IMPL-005 (prerequisito infraestructura SSL)
- ADR afectado: Ninguno
- ROADMAP-001: Impacta (transversal)
- PMP-001: Impacta (transversal)

---

# 6. Jobs y Tareas Programadas

## Estado auditado

| Componente | Estado | Detalle |
|-----------|--------|---------|
| Laravel Scheduler | ❌ Sin tareas | `artisan schedule:list` → 0 tareas |
| Queue Workers activos | ❌ Ninguno | `ps aux` → sin procesos `queue:work` |
| QUEUE_CONNECTION | `sync` | Los jobs se procesan en el request |
| Failed Jobs | 0 | Tabla `failed_jobs` vacía |
| Cron del sistema | ✅ HestiaCP | `/etc/cron.d/hestia-proc` (reboot) |

### Evaluación

Para la carga actual (116 usuarios, sin jobs asíncronos definidos), `QUEUE_CONNECTION=sync`
es suficiente. No existe riesgo operativo inmediato.

Si en el futuro se implementan notificaciones asíncronas, envío de correos en
background o procesamiento de reportes, será necesario configurar un Queue Worker
y Supervisor.

### Trazabilidad

- BASELINE-001: Sí (QUEUE_CONNECTION=sync ⚠️)
- IMPL relacionado: Ninguno (no urgente)
- ADR afectado: Ninguno
- ROADMAP-001: No impacta a corto plazo
- PMP-001: No impacta a corto plazo

---

# 7. Backups

## Estado auditado

| Fuente | Frecuencia | Última copia | Retención | Estado |
|--------|-----------|--------------|-----------|--------|
| HestiaCP — usuario `adolfo` | Diaria (5:12 AM) | 2026-06-08 | **1 copia** | ⚠️ |
| Backup manual | Puntual | 2026-06-08 | Sin automatización | ⚠️ |
| Backup `bhagamapps` (usuario separado) | Histórico | **2025-12-10** | Obsoleto | ❌ |

### Detalles

**HestiaCP backup** (`/backup/adolfo.2026-06-08_05-12-14.tar`):
- HestiaCP realiza backup diario automático del usuario `adolfo`.
- Solo se conserva **1 copia** visible. Sin retención histórica.
- Cubre: archivos web, bases de datos del usuario, configuraciones.
- El backup de 5.12 AM del 2026-06-08 es la única copia disponible.

**Backup manual** (`~/bhagamapps-backup-20260608-121301.tar.gz`):
- Creado durante IMPL-GIT-001 (recuperación de git).
- Tamaño: 5.2 MB (excluye vendor/, node_modules/).
- Es un respaldo de archivos únicamente; no incluye dump de base de datos.
- No tiene automatización ni retención.

**Backup `bhagamapps` obsoleto**:
- El usuario `bhagamapps` tiene su último backup del 2025-12-10 — hace más
  de 6 meses. Probablemente corresponde a una configuración de HestiaCP antigua.

### Evaluación de riesgo

- Una falla de servidor hoy dejaría con 1 sola copia de respaldo (la del día).
- Pérdida potencial: hasta 24 horas de cambios entre backups.
- No hay offsite/remote backup documentado.
- La base de datos con los 1,420 bienes institucionales tiene respaldo solo
  a través del backup completo de HestiaCP.

### Trazabilidad

- BASELINE-001: No mencionado explícitamente
- IMPL relacionado: Ninguno asignado
- ADR afectado: Ninguno
- ROADMAP-001: Impacta (disponibilidad y recuperación)
- PMP-001: Impacta (OE-01 estabilidad)

---

# 8. Dependencias Críticas

## Extensiones PHP (CLI 8.4 / FPM 8.3)

| Extensión | Requerida por | Estado CLI | Estado FPM |
|-----------|--------------|------------|------------|
| `pdo_mysql` | Laravel ORM | ✅ | ✅ (asumido) |
| `mbstring` | Laravel core | ✅ | ✅ |
| `openssl` | Auth, HTTPS | ✅ | ✅ |
| `gd` | Imágenes | ✅ | ✅ |
| `curl` | HTTP clients | ✅ | ✅ |
| `zip` | Archivos | ✅ | ✅ |
| `dom` | XML/HTML | ✅ | ✅ |
| `exif` | Imágenes | ✅ | ✅ |
| `imagick` | Imágenes avanzadas | ✅ | ✅ |
| `intl` | Internacionalización | ✅ | ✅ |
| `sodium` | Cifrado moderno | ✅ | ✅ |

## Composer

- `composer.json` y `composer.lock` presentes y consistentes.
- `vendor/` instalado (187 MB excluido de git según .gitignore).
- No se ejecutó `composer outdated` (fuera de alcance de esta auditoría).

## Vite build

Vite manifest presente y válido en
`public/build/manifest.json`. Los errores de logs del 2026-06-07 sobre
manifest faltante son previos al build actual y ya no aplican.

### Trazabilidad

- BASELINE-001: Sí (§ Stack)
- IMPL relacionado: Ninguno
- ADR afectado: ADR-001
- ROADMAP-001: No impacta
- PMP-001: No impacta

---

# 9. Riesgos de Producción — Hallazgos

## H-001 — Sin redirección HTTP → HTTPS

**Riesgo:** Alto
**Categoría:** Seguridad

El servidor no redirige el tráfico HTTP a HTTPS. Todo el tráfico entre usuarios
y la aplicación circula sin cifrar. Credenciales de sesión, contraseñas y
datos institucionales viajan en texto plano.

### Trazabilidad
- BASELINE-001: Sí — con inconsistencia (BASELINE-001 dijo "corre bajo HTTPS", la realidad es HTTP)
- IMPL relacionado: IMPL-005 (prerequisito SSL)
- ADR afectado: Ninguno
- ROADMAP-001: Impacta
- PMP-001: Impacta

---

## H-002 — HTTPS no operativo para bhagamapps.com

**Riesgo:** Alto
**Categoría:** Seguridad / Infraestructura

No existe configuración SSL/HTTPS específica para el dominio `bhagamapps.com`
en HestiaCP. Los archivos `nginx.forcessl.conf` y `apache2.forcessl.conf`
no existen en `/home/adolfo/conf/web/bhagamapps.com/`.

El default server de nginx para el puerto 443 usa el certificado del servidor
(`vmi2865722.contaboserver.net`, Let's Encrypt, válido hasta 2026-07-18) y
redirige **de HTTPS de vuelta a HTTP**. Un usuario que intente acceder por
HTTPS es activamente redirigido al protocolo inseguro.

`APP_URL=https://bhagamapps.com/Modular` en `.env` es inconsistente con la
infraestructura real.

### Acción requerida

Activar SSL para `bhagamapps.com` desde HestiaCP. Esta es una acción de servidor,
no de código. Prerequisito para IMPL-005.

### Trazabilidad
- BASELINE-001: Inconsistencia — afirmó HTTPS activo cuando no lo está
- IMPL relacionado: IMPL-005 (dependiente de este hallazgo)
- ADR afectado: Ninguno
- ROADMAP-001: Impacta (Fase 1 — riesgos críticos)
- PMP-001: Impacta (OE-04)

---

## H-003 — Archivos de diagnóstico ejecutables en `public/`

**Riesgo:** Alto
**Categoría:** Seguridad

Tres archivos de diagnóstico son accesibles públicamente en
`/home/adolfo/web/bhagamapps.com/private/bhagamappsModular/public/`:

| Archivo | Contenido | Riesgo |
|---------|-----------|--------|
| `test.php` | `if (function_exists('proc_open'))` — diagnóstico | Medio |
| `info.php` | `var_dump(function_exists('proc_open'))` | Medio |
| `test_proc_open.php` | `proc_open('ls', [...], $pipes)` — **ejecuta `ls`** | **Alto** |

`test_proc_open.php` ejecuta el comando `ls` en el servidor y devuelve el
resultado al navegador. El directorio de trabajo de Apache2 al ejecutar PHP-FPM
podría exponer rutas y nombres de archivos del servidor.

Dado que el directorio `public/` está mapeado como:
```
http://bhagamapps.com/Modular/ → public/
```

Las URLs
`http://bhagamapps.com/Modular/test_proc_open.php` y
`http://bhagamapps.com/Modular/test.php` son accesibles públicamente en este
momento.

Estos archivos fueron creados para diagnosticar `proc_open` durante la
configuración inicial y no fueron eliminados antes de entrar en operación.

### Trazabilidad
- BASELINE-001: No mencionado
- IMPL relacionado: IMPL-009 (eliminar TestFiltroController) — naturaleza similar
- ADR afectado: Ninguno
- ROADMAP-001: No impacta
- PMP-001: Impacta (OE-04 seguridad)

---

## H-004 — SESSION_SECURE_COOKIE deshabilitado

**Riesgo:** Medio (bloqueado por H-002)
**Categoría:** Seguridad

Las cookies de sesión no tienen el atributo `Secure`. Los navegadores modernos
pueden enviar estas cookies tanto por HTTP como por HTTPS, lo que permite
interceptación.

Este hallazgo no puede resolverse hasta que H-002 (HTTPS) esté resuelto.
Habilitar `SESSION_SECURE_COOKIE=true` sin HTTPS activo haría que las sesiones
no funcionen (el navegador no enviará cookies Secure sobre HTTP).

### Estado de IMPL-005

PENDIENTE — bloqueado por H-002.

### Trazabilidad
- BASELINE-001: Sí
- IMPL relacionado: IMPL-005
- ADR afectado: Ninguno
- ROADMAP-001: Impacta
- PMP-001: Impacta

---

## H-005 — MAIL_MAILER no funcional

**Riesgo:** Medio
**Categoría:** Operativo

`MAIL_MAILER=log`. El flujo de recuperación de contraseña de Fortify no puede
enviar emails. 116 usuarios no tienen mecanismo de recuperación funcional.

Las rutas de Fortify para password reset (`/forgot-password`, `/reset-password`)
están disponibles en el código pero el mailer las registra en log en lugar de
enviar.

### Estado de IMPL-006

PENDIENTE — independiente de H-002 (puede ejecutarse con o sin HTTPS).

Opciones evaluadas en BASELINE-001:
- Servidor propio del dominio (HestiaCP Exim)
- Gmail SMTP (requiere App Password)
- Mailgun (requiere cuenta)

### Trazabilidad
- BASELINE-001: Sí (MAIL_MAILER = log ❌)
- IMPL relacionado: IMPL-006
- ADR afectado: Ninguno
- ROADMAP-001: Impacta
- PMP-001: Impacta (OE-01)

---

## H-006 — Desajuste de versión PHP CLI vs FPM

**Riesgo:** Medio
**Categoría:** Operativo / Mantenimiento

| Contexto | Versión |
|----------|---------|
| PHP CLI (`php artisan`) | 8.4.14 |
| PHP-FPM (requests web) | 8.3.27 |

El socket de FPM en uso es `php8.3-fpm-bhagamapps.com.sock`. Las extensiones
instaladas en CLI (8.4) y FPM (8.3) pueden diferir. Los tests con `php artisan`
pueden pasar en CLI 8.4 pero fallar en web con FPM 8.3 (o viceversa).

Impacto actual: bajo (ambas versiones son compatibles con Laravel 11). El riesgo
aumenta si se usan características específicas de PHP 8.4 en el código.

### Trazabilidad
- BASELINE-001: Sí (PHP 8.4.14) — versión CLI documentada, FPM no identificado
- IMPL relacionado: Ninguno asignado
- ADR afectado: ADR-001
- ROADMAP-001: No impacta directamente
- PMP-001: No impacta directamente

---

## H-007 — Disco al 82% de capacidad

**Riesgo:** Medio
**Categoría:** Disponibilidad

```
/dev/sda1   242G   197G   45G  82%  /
```

Con 45 GB disponibles y el sistema en operación activa:
- `vendor/` ocupa ~187 MB
- `node_modules/` ocupa ~73 MB
- Logs y backups crecen con el tiempo

El umbral de alerta habitual es 80%. El sistema ya lo superó. Sin monitoreo
activo de disco, un llenado completo derribaría la aplicación y la base de datos.

**No hay swap configurado.** Si la RAM se agotara (poco probable con 39 GB libres
actualmente), el kernel haría OOM kill sin respaldo de swap.

### Trazabilidad
- BASELINE-001: No mencionado
- IMPL relacionado: Ninguno asignado
- ADR afectado: Ninguno
- ROADMAP-001: Impacta (disponibilidad)
- PMP-001: Impacta (OE-01)

---

## H-008 — Retención de backups insuficiente

**Riesgo:** Medio
**Categoría:** Recuperación

Solo existe 1 copia de backup de HestiaCP (`adolfo.2026-06-08`). Si el backup
del día tiene error o el servidor falla antes de las 5 AM, no hay respaldo previo
del usuario `adolfo`.

El backup manual (`bhagamapps-backup-20260608-121301.tar.gz`) cubre solo
archivos de código (excluyó `vendor/`), no la base de datos.

### Trazabilidad
- BASELINE-001: No mencionado
- IMPL relacionado: Ninguno asignado
- ADR afectado: Ninguno
- ROADMAP-001: Impacta
- PMP-001: Impacta (OE-01)

---

## H-009 — Errores de rutas duplicadas en logs

**Riesgo:** Bajo
**Categoría:** Estabilidad

En `laravel.log` aparecen dos tipos de errores de serialización de rutas:

```
Unable to prepare route [profile] for serialization.
Another route has already been assigned name [profile.show].

Unable to prepare route [Modular/livewire/update] for serialization.
Another route has already been assigned name [livewire.update].
```

Estos errores ocurren cuando se ejecuta `php artisan route:list`. No afectan
la operación normal de la aplicación (las rutas funcionan). Son síntoma de
definición de rutas duplicadas entre el core de Laravel/Jetstream y las rutas
del módulo.

El error de `InventarioController not found` del 2026-06-07 corresponde a
un estado anterior a la configuración actual y ya no ocurre.

### Trazabilidad
- BASELINE-001: No mencionado
- IMPL relacionado: Ninguno asignado
- ADR afectado: ADR-001
- ROADMAP-001: No impacta operacionalmente
- PMP-001: No impacta

---

# 10. Trazabilidad Documental — Resumen

| Hallazgo | BASELINE-001 | IMPL relacionado | ADR | ROADMAP-001 | PMP-001 |
|----------|-------------|-----------------|-----|-------------|---------|
| H-001 HTTPS sin redirect | Sí (inconsistencia) | IMPL-005 | Ninguno | Impacta | Impacta |
| H-002 HTTPS no operativo | Sí (error) | IMPL-005 | Ninguno | Impacta | Impacta |
| H-003 Archivos exec en public/ | No | Ninguno | Ninguno | No impacta | Impacta |
| H-004 SESSION_SECURE | Sí | IMPL-005 | Ninguno | Impacta | Impacta |
| H-005 MAIL no funcional | Sí | IMPL-006 | Ninguno | Impacta | Impacta |
| H-006 PHP CLI vs FPM | Sí (parcial) | Ninguno | ADR-001 | No impacta | No impacta |
| H-007 Disco 82% | No | Ninguno | Ninguno | Impacta | Impacta |
| H-008 Backups insuficientes | No | Ninguno | Ninguno | Impacta | Impacta |
| H-009 Rutas duplicadas | No | Ninguno | ADR-001 | No impacta | No impacta |

---

# 11. Matriz de Hallazgos

| ID | Hallazgo | Riesgo | Impacto | IMPL relacionado | Requiere PLAN-IMPL |
|----|----------|--------|---------|------------------|--------------------|
| H-001 | Sin redirección HTTP → HTTPS | Alto | Seguridad | IMPL-005 | Sí |
| H-002 | HTTPS no operativo en dominio | Alto | Seguridad + Infra | IMPL-005 | Sí |
| H-003 | Archivos diagnóstico en public/ | Alto | Seguridad | Ninguno | Sí (urgente) |
| H-004 | SESSION_SECURE_COOKIE desactivado | Medio | Seguridad | IMPL-005 | No (depende de H-002) |
| H-005 | MAIL_MAILER=log | Medio | Operativo | IMPL-006 | Sí |
| H-006 | PHP CLI 8.4 vs FPM 8.3 | Medio | Mantenimiento | Ninguno | No (monitorear) |
| H-007 | Disco al 82% | Medio | Disponibilidad | Ninguno | Sí |
| H-008 | Retención de backups mínima | Medio | Recuperación | Ninguno | Sí |
| H-009 | Rutas duplicadas en logs | Bajo | Estabilidad | Ninguno | No (monitorear) |

---

# 12. Clasificación Final

## Recomendación

**Crear PLAN-IMPL con acciones prioritarias agrupadas.**

### Justificación técnica

**H-003 requiere acción inmediata**, independiente de cualquier PLAN-IMPL:
`test_proc_open.php` ejecuta comandos de shell desde el contexto de Apache2
y es accesible públicamente vía HTTP. Es el único hallazgo de esta auditoría
que no requiere aprobación de Dirección General para comenzar — la eliminación
de archivos de diagnóstico de `public/` es una acción correctiva obvia y urgente.

**H-001 y H-002** (HTTPS) son el segundo bloqueo más urgente. Afectan la
confidencialidad de las credenciales de todos los usuarios en cada sesión.
La acción es una configuración en HestiaCP (habilitar SSL para el dominio),
no un cambio de código. IMPL-005 y su prerequisito SSL deben abordarse juntos.

**H-005** (correo) puede abordarse independientemente de HTTPS. Afecta la
recuperación de contraseñas pero no la seguridad de las sesiones activas.

**H-007 y H-008** (disco y backups) son riesgos operativos que no tienen
bloqueadores técnicos.

### PLAN-IMPL sugerido

Se recomienda un único PLAN-IMPL que agrupe:

```
PLAN-IMPL-009
Production Security and Infrastructure Readiness

Acciones:
  P1 — H-003: Eliminar archivos diagnóstico de public/
  P2 — H-001/H-002: Habilitar SSL en HestiaCP + IMPL-005
  P3 — H-005: Configurar MAIL_MAILER SMTP (IMPL-006)
  P4 — H-007/H-008: Limpiar disco + configurar retención de backups
```

Alternativamente, los hallazgos H-001/H-002/H-004 pueden ejecutarse como
**IMPL-005** directamente (ya tienen número asignado en BASELINE-001) con
un PLAN-IMPL-005 previo.

---

# Inconsistencias Documentales Detectadas

## Inconsistencia con BASELINE-001

| Campo | BASELINE-001 dice | Realidad auditada |
|-------|------------------|-------------------|
| Protocolo HTTP/HTTPS | "El sitio corre bajo HTTPS" | HTTP únicamente |
| SSL | Pendiente de habilitar en HestiaCP | Nunca habilitado |

BASELINE-001 § "Configuración de producción" → `SESSION_SECURE_COOKIE: comentado ⚠️`
combinado con el texto "corre bajo HTTPS" sugirió que HTTPS funcionaba pero
el flag Secure estaba pendiente. La auditoría demuestra que ninguno de los dos
está activo.

Esta inconsistencia puede resolverse mediante una nota aclaratoria en
BASELINE-001 o en el cierre de esta auditoría.

---

# Estado de IMPL Auditados

| IMPL | Descripción | Estado auditado |
|------|-------------|----------------|
| IMPL-005 | Habilitar SESSION_SECURE_COOKIE | **PENDIENTE** — bloqueado por H-002 |
| IMPL-006 | Configurar MAIL_MAILER SMTP | **PENDIENTE** — independiente, ejecutable |

---

# Firmas y Aprobación

**Estado:** BORRADOR

Este documento requiere revisión y aprobación de Dirección General antes de
ser considerado oficial.

**Preparado por:** Auditoría — 2026-06-08

**Pendiente de aprobación:** Dirección General
