# IMPL-006 — SMTP Configuration and Mail Delivery

**Estado:** AUTORIZADA — EN DIAGNÓSTICO
**Fecha:** 2026-06-08
**Origen:** AUDIT-006 → PLAN-IMPL-010 → DG-016
**Responsable:** Implementación

---

# Contexto

AUDIT-006 identificó:

H-005 — MAIL_MAILER=log

Actualmente la aplicación no realiza entrega real de correo electrónico.

---

# Objetivo

Habilitar envío SMTP funcional en producción para BhagamAppsModular.

---

# Diagnóstico Inicial — Ejecutado 2026-06-08

## Variables .env (estado actual)

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@bhagamapps.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Variables MAIL ausentes del `.env`** (no configuradas):

```env
MAIL_HOST      → no definido
MAIL_PORT      → no definido
MAIL_USERNAME  → no definido
MAIL_PASSWORD  → no definido
MAIL_ENCRYPTION → no definido
```

### Análisis del estado actual

| Variable | Valor actual | Estado |
|----------|-------------|--------|
| `MAIL_MAILER` | `log` | ❌ No envía correos reales |
| `MAIL_HOST` | No configurado | ❌ Sin host SMTP |
| `MAIL_PORT` | No configurado | ❌ Sin puerto |
| `MAIL_USERNAME` | No configurado | ❌ Sin credenciales |
| `MAIL_PASSWORD` | No configurado | ❌ Sin credenciales |
| `MAIL_ENCRYPTION` | No configurado | ❌ Sin cifrado definido |
| `MAIL_FROM_ADDRESS` | `noreply@bhagamapps.com` | ✅ Dirección institucional configurada |
| `MAIL_FROM_NAME` | `${APP_NAME}` | ✅ Referencia dinámica al nombre de la app |

---

## Infraestructura de correo del servidor

El servidor tiene un MTA local activo:

```text
Exim 4.97 #2 (built 27-May-2026)
primary_hostname = vmi2865722.contaboserver.net
```

### Puertos activos

| Puerto | Protocolo | Estado |
|--------|-----------|--------|
| 25 | SMTP | ✅ Activo |
| 465 | SMTPS | ✅ Activo |
| 587 | SMTP+STARTTLS | ✅ Activo |
| 993 | IMAPS | ✅ Activo |
| 995 | POP3S | ✅ Activo |

### Prueba de conexión local

```
$ echo QUIT | nc localhost 587
220 localhost ESMTP
221 localhost closing connection
```

**Exim responde correctamente en localhost:587.**

---

## DNS relacionado con correo — bhagamapps.com

| Registro | Valor | Estado |
|----------|-------|--------|
| MX | **No existe** | ❌ Sin servidor de correo receptor |
| TXT / SPF | **No existe** | ❌ Sin política de envío |
| DKIM | **No existe** | ❌ Sin firma de dominio |
| PTR (147.93.176.252) | `vmi2865722.contaboserver.net` | ⚠️ PTR apunta al hostname del servidor, no al dominio |

---

## Conclusión del diagnóstico

La configuración actual tiene **cero infraestructura de correo configurada**:

* `.env` solo define `MAIL_MAILER=log` y la dirección remitente.
* No hay host, puerto, credenciales ni cifrado configurados.
* No hay registros DNS de correo (MX, SPF, DKIM).
* El servidor SÍ tiene Exim 4.97 funcionando localmente.

---

# Opciones de Proveedor SMTP

## Opción A — Exim local (SMTP localhost)

Configuración mínima:

```env
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
```

O alternativamente vía sendmail:

```env
MAIL_MAILER=sendmail
```

Ventajas:
* Sin costo adicional.
* Sin proveedor externo.
* Exim ya está instalado y respondiendo.

Consideraciones:
* Entregabilidad depende de reputación de IP `147.93.176.252`.
* Sin SPF ni DKIM, mayor riesgo de clasificación como spam.
* Requiere configurar SPF en GoDaddy DNS para `bhagamapps.com`.

## Opción B — SMTP externo (Gmail, Mailgun, Brevo, etc.)

Ventajas:
* Alta entregabilidad garantizada.
* Infraestructura de reputación del proveedor.
* DKIM y SPF gestionados por el proveedor.

Requerimientos:
* Cuenta y credenciales del proveedor seleccionado.
* Registros DNS SPF y DKIM según proveedor.

## Opción C — SMTP institucional

Si la institución posee servidor de correo propio:
* Host, puerto y credenciales institucionales.

---

# Dependencias para Completar IMPL-006

Para proceder a la Fase 2 (configuración), Dirección General debe proveer:

1. **Selección de proveedor SMTP** (Opción A, B o C).
2. Si Opción B o C: **credenciales SMTP** del proveedor.
3. Si Opción B: **registros DNS** que indique el proveedor (SPF, DKIM).

---

# Actividades Pendientes (post-diagnóstico)

## Fase 2 — Configuración

Actualizar `.env` según proveedor seleccionado.

## Fase 3 — Validación

Prueba de envío controlada:

```bash
php artisan tinker
Mail::raw('Test IMPL-006', function($m) {
    $m->to('destinatario@dominio.com')->subject('IMPL-006 Test');
});
```

Validar entrega en destino.

## Fase 4 — Verificación Funcional

* Laravel Mail sin errores en logs.
* Sin impacto sobre autenticación o sesiones.

---

# Exclusiones

No incluye:

* HTTPS.
* SSL web.
* SESSION_SECURE_COOKIE.
* Infraestructura PHP.
* Backups.
* Limpieza operativa.
* Optimización de logs.

---

# Riesgos

## R-01

Sin credenciales ni proveedor seleccionado aún.

Impacto: Fases 2-4 bloqueadas hasta decisión de DG.

## R-02

Entregabilidad baja con Exim local sin SPF/DKIM.

Mitigación: Agregar SPF en GoDaddy DNS al seleccionar Opción A.

## R-03

Correos en spam por IP sin reputación establecida.

Mitigación: Considerar proveedor externo (Opción B) si entregabilidad es prioritaria.

---

# Criterios de Éxito

* `MAIL_MAILER=smtp` (o `sendmail`).
* Correo de prueba enviado correctamente.
* Evidencia documentada.
* Hallazgo H-005 cerrado.

---

# Hallazgo que Cierra

H-005 — MAIL_MAILER=log

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
