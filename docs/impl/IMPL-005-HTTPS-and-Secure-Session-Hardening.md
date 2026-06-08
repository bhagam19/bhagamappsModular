# IMPL-005 — HTTPS and Secure Session Hardening

**Estado:** SUSPENDIDA TEMPORALMENTE
**Fecha:** 2026-06-08
**Origen:** AUDIT-006 → PLAN-IMPL-010 → DG-015
**Responsable:** Implementación

> **Estado actual:** SUSPENDIDA TEMPORALMENTE
> **Motivo:** Bloqueo externo DNS / Let's Encrypt
> **Hallazgo relacionado:** H-002
> **Dependencia:** Configuración de registro CAA en GoDaddy DNS
>
> La emisión del certificado SSL falló con:
> `DNS problem: SERVFAIL looking up CAA for com`
>
> Causa raíz identificada: `www.bhagamapps.com` es un CNAME a `bhagamapps.com`.
> Let's Encrypt recorre el árbol CAA hasta `com.` al no encontrar registro CAA
> en `bhagamapps.com`. El SERVFAIL fue transitorio en los servidores TLD `.com`.
>
> Corrección requerida: agregar registro CAA en GoDaddy DNS para `bhagamapps.com`:
> `0 issue "letsencrypt.org"`
>
> Una vez propagado el registro CAA, reintentar emisión en HestiaCP.

---

# Contexto

AUDIT-006 identificó deficiencias de seguridad relacionadas con la protección del tráfico web y la gestión de sesiones de usuario en producción.

Hallazgos asociados:

* H-001 — Sin redirección HTTP → HTTPS
* H-002 — HTTPS no operativo para bhagamapps.com
* H-004 — SESSION_SECURE_COOKIE desactivado

Estos hallazgos afectan directamente la confidencialidad e integridad de las sesiones de usuario.

---

# Objetivo

Implementar y validar HTTPS para BhagamAppsModular y endurecer la configuración de sesiones para garantizar que las cookies de autenticación sean transmitidas únicamente mediante conexiones seguras.

---

# Alcance

## HTTPS

Verificar y corregir:

* Certificado SSL.
* Configuración HestiaCP.
* Configuración VirtualHost.
* APP_URL.
* Redirección HTTP → HTTPS.

---

## Sesiones

Configurar:

```env
SESSION_SECURE_COOKIE=true
```

Verificar además:

```env
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

---

# Diagnóstico Inicial — Ejecutado 2026-06-08

## Dominio

| Nombre | Resolución |
|--------|-----------|
| `bhagamapps.com` | `147.93.176.252` ✅ |
| `www.bhagamapps.com` | CNAME → `bhagamapps.com` → `147.93.176.252` ✅ |

## HTTPS

| Parámetro | Estado |
|-----------|--------|
| Puerto 443 | Activo |
| Certificado | `CN=vmi2865722.contaboserver.net` — NO válido para `bhagamapps.com` |
| Emisor | Let's Encrypt (servidor, no dominio) |
| Expiración | 2026-07-18 |
| SAN bhagamapps.com | ❌ Ausente |
| Comportamiento actual | HTTPS → 301 → HTTP (redirección inversa) |

## HestiaCP

| Parámetro | Estado |
|-----------|--------|
| SSL config para bhagamapps.com | ❌ No existe |
| `nginx.forcessl.conf` | ❌ No existe |
| `apache2.forcessl.conf` | ❌ No existe |
| Directorio SSL | ❌ `/home/adolfo/conf/web/bhagamapps.com/ssl/` — No existe |

## Variables Laravel (.env)

```env
APP_URL=https://bhagamapps.com/Modular   ← ya apunta a HTTPS ✅
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/Modular
SESSION_DOMAIN=bhagamapps.com
# SESSION_SECURE_COOKIE=true              ← comentado ❌
```

## config/session.php (valores fijos)

```php
'http_only' => true,    // ✅ correcto
'same_site' => 'lax',   // ✅ correcto
```

## Cookies actuales (evidencia curl)

```
Set-Cookie: bhagamapps-session=...; path=/Modular; httponly; samesite=lax
```

* `httponly` ✅
* `samesite=lax` ✅
* `secure` ❌ ausente — pendiente de HTTPS

## DNS

| Parámetro | Estado |
|-----------|--------|
| Nameservers | `ns27.domaincontrol.com` / `ns28.domaincontrol.com` (GoDaddy) |
| DNSSEC | No habilitado ✅ |
| DS record en `.com` | Ausente ✅ |
| Registro CAA | **NO EXISTE** ⚠️ |
| SOA serial | `2025121000` (diciembre 2025) |

---

# Bloqueo — Error Let's Encrypt

## Error reportado

```
DNS problem: SERVFAIL looking up CAA for com
```

## Causa raíz

`www.bhagamapps.com` es un `CNAME` a `bhagamapps.com`.

Let's Encrypt (RFC 8659) recorre el árbol CAA para `www.bhagamapps.com`:

1. `www.bhagamapps.com` → CNAME → no hay CAA
2. `bhagamapps.com` → no hay registro CAA (NOERROR + SOA)
3. Escala a `com.` → SERVFAIL transitorio en servidor TLD

Al no existir registro CAA en `bhagamapps.com`, LE continúa el walk hasta `.com`
y expone la emisión a fallos transitorios de los nameservers TLD.

## Corrección requerida (pendiente de Dirección General)

Agregar en GoDaddy DNS → `bhagamapps.com` → Registros:

| Tipo | Host | Valor | TTL |
|------|------|-------|-----|
| `CAA` | `@` | `0 issue "letsencrypt.org"` | 3600 |
| `CAA` | `@` | `0 issuewild "letsencrypt.org"` | 3600 |

Verificar propagación:

```bash
dig bhagamapps.com CAA +short
# Resultado esperado: 0 issue "letsencrypt.org"
```

Reintentar emisión SSL en HestiaCP una vez propagado.

---

# Actividades Pendientes (post-desbloqueo)

## Fase 2 — Corrección HTTPS

* Emitir certificado SSL con Let's Encrypt para `bhagamapps.com` y `www.bhagamapps.com`.
* Verificar renovación automática.
* Configurar redirección HTTP → HTTPS en HestiaCP.

## Fase 3 — Hardening de Sesiones

Una vez HTTPS validado:

```env
SESSION_SECURE_COOKIE=true
```

## Fase 4 — Validación Funcional

* Login / Logout.
* Navegación.
* Livewire.
* Cookies con atributo `Secure`.

---

# Archivos Potencialmente Modificados (pendientes)

```text
.env
Configuración HestiaCP
Configuración VirtualHost
```

---

# Exclusiones

No incluye:

* SMTP.
* MAIL_MAILER.
* Infraestructura PHP.
* Backups.
* Gestión de disco.
* Limpieza operativa.
* Optimización de logs.

---

# Riesgos

## R-01

Configuración incorrecta de SSL.

Mitigación: Respaldo previo de configuración.

## R-02

Configuración incorrecta de cookies.

Mitigación: Validación completa de login y navegación.

## R-03

Redirecciones incorrectas.

Mitigación: Pruebas con curl y navegador.

---

# Criterios de Éxito

* HTTPS operativo.
* Certificado SSL válido para `bhagamapps.com`.
* HTTP redireccionado automáticamente a HTTPS.
* `SESSION_SECURE_COOKIE=true`.
* Cookies `Secure` y `HttpOnly` activas.
* Login funcional.
* Livewire funcional.
* Hallazgos H-001, H-002 y H-004 cerrados.

---

# Hallazgos que Cierra

```text
H-001 — Sin redirección HTTP → HTTPS
H-002 — HTTPS no operativo
H-004 — SESSION_SECURE_COOKIE desactivado
```

---

# Trazabilidad

```text
AUDIT-006
↓
PLAN-IMPL-010
↓
DG-015
↓
IMPL-005
```
