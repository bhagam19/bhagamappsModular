# DG-016 — Ejecución de IMPL-006 SMTP Configuration and Mail Delivery

**Estado:** APROBADA
**Fecha:** 2026-06-08
**Autoridad:** Dirección General

---

# Contexto

AUDIT-006 — Production Configuration and Infrastructure Readiness identificó el siguiente hallazgo pendiente:

* H-005 — MAIL_MAILER=log (correo electrónico no funcional)

Actualmente BhagamAppsModular registra los correos en logs y no realiza entrega real de mensajes.

Esto impide la futura implementación de:

* Recuperación de contraseña.
* Notificaciones automáticas.
* Confirmaciones por correo.
* Alertas institucionales.

---

# Decisión

Dirección General autoriza la ejecución de:

**IMPL-006 — SMTP Configuration and Mail Delivery**

con el objetivo de habilitar el envío real de correo electrónico desde producción.

---

# Alcance

## Diagnóstico

Verificar:

* MAIL_MAILER actual.
* Configuración SMTP existente.
* Credenciales disponibles.
* Configuración DNS relacionada con correo.

## Configuración

Implementar proveedor SMTP aprobado.

Puede incluir:

* SMTP institucional.
* SMTP de hosting.
* SMTP transaccional.

La selección deberá basarse en evidencia técnica.

## Validación

Realizar pruebas controladas de envío.

Documentar:

* Configuración utilizada.
* Resultado de pruebas.
* Riesgos identificados.

---

# Exclusiones

No incluye:

* HTTPS.
* SSL web.
* SESSION_SECURE_COOKIE.
* Infraestructura PHP.
* Backups.
* Limpieza operativa.

---

# Criterios de Éxito

* MAIL_MAILER deja de utilizar log.
* SMTP funcional.
* Correo de prueba entregado correctamente.
* Evidencia documentada.
* Sin regresiones funcionales.

---

# Hallazgo Objetivo

Cerrar formalmente:

H-005 — MAIL_MAILER=log

identificado en AUDIT-006.

---

# Trazabilidad

```text
AUDIT-006
↓
PLAN-IMPL-010
↓
DG-016
↓
IMPL-006
```

---

# Estado Final

DG-016

Ejecución de IMPL-006 SMTP Configuration and Mail Delivery

Estado: APROBADA
