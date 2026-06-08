# DG-015 — Ejecución de IMPL-005 HTTPS and Secure Session Hardening

**Estado:** APROBADA
**Fecha:** 2026-06-08
**Autoridad:** Dirección General

---

# Contexto

AUDIT-006 — Production Configuration and Infrastructure Readiness identificó los siguientes hallazgos abiertos:

* H-001 — Sin redirección HTTP → HTTPS
* H-002 — HTTPS no operativo para bhagamapps.com
* H-004 — SESSION_SECURE_COOKIE desactivado

Estos hallazgos representan el mayor riesgo de seguridad actualmente pendiente en producción y fueron incorporados al:

PLAN-IMPL-010 — Production Security and Infrastructure Hardening

---

# Decisión

Dirección General autoriza la ejecución de:

**IMPL-005 — HTTPS and Secure Session Hardening**

con el objetivo de asegurar las comunicaciones entre clientes y servidor y endurecer la gestión de sesiones de BhagamAppsModular.

---

# Alcance

## Seguridad HTTPS

Verificar y corregir:

* Certificado SSL.
* Configuración HestiaCP.
* VirtualHost.
* APP_URL.
* Redirección HTTP → HTTPS.

## Seguridad de Sesiones

Una vez validado HTTPS:

```env
SESSION_SECURE_COOKIE=true
```

Verificar además:

```env
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

---

# Hallazgos Objetivo

La implementación deberá cerrar formalmente:

* H-001 — Sin redirección HTTP → HTTPS
* H-002 — HTTPS no operativo
* H-004 — SESSION_SECURE_COOKIE desactivado

identificados en AUDIT-006.

---

# Exclusiones

Esta autorización NO incluye:

* SMTP.
* MAIL_MAILER.
* Configuración de correo.
* PHP CLI/FPM.
* Gestión de backups.
* Espacio en disco.
* Limpieza operativa.
* Optimización de logs.

Estos elementos permanecen bajo:

* IMPL-006 — SMTP Configuration and Mail Delivery
* IMPL-010 — Infrastructure Alignment and Capacity Management
* IMPL-011 — Operational Cleanup and Log Normalization

---

# Criterios de Éxito

Se considerará completada la implementación cuando:

* HTTPS esté operativo.
* El certificado SSL sea válido.
* HTTP redireccione automáticamente a HTTPS.
* SESSION_SECURE_COOKIE esté habilitado.
* Las cookies de sesión utilicen atributos Secure y HttpOnly.
* No existan regresiones funcionales.
* La aplicación funcione correctamente bajo HTTPS.
* Se genere documentación formal de implementación.

---

# Entregables Requeridos

La ejecución deberá producir:

* IMPL-005 — HTTPS and Secure Session Hardening.
* Evidencias de validación HTTPS.
* Evidencias de validación de cookies.
* Actualización de changelogs según política vigente.
* Actualización de versionado según política vigente.

---

# Trazabilidad

Origen:

```text
AUDIT-006
↓
PLAN-IMPL-010
↓
DG-015
↓
IMPL-005
```

Documentos relacionados:

* AUDIT-006
* PLAN-IMPL-010
* IMPL-005
* BASELINE-001
* ROADMAP-001
* PMP-001

---

# Estado Final

DG-015

Ejecución de IMPL-005 HTTPS and Secure Session Hardening

Estado: APROBADA
Aplicación: Inmediata
