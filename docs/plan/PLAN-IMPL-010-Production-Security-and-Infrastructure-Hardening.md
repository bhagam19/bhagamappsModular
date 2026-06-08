# PLAN-IMPL-010 — Production Security and Infrastructure Hardening

**Estado:** APROBADO
**Fecha:** 2026-06-08
**Origen:** AUDIT-006 — Production Configuration and Infrastructure Readiness
**Responsable:** Dirección General / Implementación

---

# Contexto

AUDIT-006 evaluó el estado real de configuración e infraestructura de producción de BhagamAppsModular.

Como resultado se identificaron nueve hallazgos, de los cuales uno (H-003) fue corregido inmediatamente mediante:

```text
DG-014
↓
IMPL-009
Remove Public Diagnostic Files
```

Los hallazgos restantes afectan:

* Seguridad de transporte (HTTPS)
* Seguridad de sesiones
* Configuración de correo
* Infraestructura de servidor
* Estrategia de respaldos
* Mantenimiento operativo

---

# Objetivo

Elevar el nivel de preparación de producción de BhagamAppsModular mediante la corrección gradual de los hallazgos pendientes identificados por AUDIT-006.

---

# Hallazgos Incluidos

## Prioridad Alta

### H-001

Sin redirección HTTP → HTTPS.

Riesgo:

```text
Alto
```

---

### H-002

HTTPS no operativo para bhagamapps.com.

Riesgo:

```text
Alto
```

---

### H-004

SESSION_SECURE_COOKIE desactivado.

Riesgo:

```text
Medio
```

Dependencia:

```text
No debe activarse hasta que HTTPS funcione correctamente.
```

---

## Prioridad Media

### H-005

MAIL_MAILER=log.

Riesgo:

```text
Medio
```

Consecuencia:

```text
El sistema no puede enviar correos reales.
```

---

### H-006

PHP CLI 8.4 vs PHP-FPM 8.3.

Riesgo:

```text
Medio
```

Consecuencia:

```text
Comportamiento inconsistente entre consola y entorno web.
```

---

### H-007

Uso de disco al 82%.

Riesgo:

```text
Medio
```

Capacidad libre:

```text
45 GB
```

---

### H-008

Retención mínima de backups.

Riesgo:

```text
Medio
```

Situación:

```text
Solo una copia disponible.
```

---

## Prioridad Baja

### H-009

Rutas duplicadas en logs.

Riesgo:

```text
Bajo
```

Impacto:

```text
Mantenibilidad.
```

---

# Estrategia de Implementación

La ejecución deberá realizarse en fases independientes para minimizar riesgos.

---

# Fase 1 — Seguridad HTTPS

## Implementación asociada

```text
IMPL-005
HTTPS and Secure Session Hardening
```

## Alcance

### HTTPS

Verificar y corregir:

* Certificado SSL
* Configuración HestiaCP
* VirtualHost
* Redirección HTTP → HTTPS
* APP_URL

### Sesiones

Una vez validado HTTPS:

```env
SESSION_SECURE_COOKIE=true
```

Verificar además:

```env
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

## Hallazgos que cierra

```text
H-001
H-002
H-004
```

---

# Fase 2 — Correo Electrónico

## Implementación asociada

```text
IMPL-006
SMTP Configuration and Mail Delivery
```

## Alcance

Configurar:

```env
MAIL_MAILER=smtp
MAIL_HOST
MAIL_PORT
MAIL_USERNAME
MAIL_PASSWORD
MAIL_ENCRYPTION
MAIL_FROM_ADDRESS
```

Realizar prueba controlada de envío.

## Hallazgos que cierra

```text
H-005
```

---

# Fase 3 — Infraestructura Operativa

## Implementación asociada

```text
IMPL-010
Infrastructure Alignment and Capacity Management
```

## Alcance

### PHP

Unificar:

```text
PHP CLI
PHP-FPM
```

en la misma versión soportada.

---

### Disco

Analizar:

* Logs
* Backups
* Archivos temporales

Definir política de crecimiento.

---

### Backups

Implementar:

* Múltiples generaciones
* Retención configurable
* Verificación periódica

## Hallazgos que cierra

```text
H-006
H-007
H-008
```

---

# Fase 4 — Mantenimiento

## Implementación asociada

```text
IMPL-011
Operational Cleanup and Log Normalization
```

## Alcance

* Revisar duplicidades de rutas.
* Limpiar registros redundantes.
* Mejorar trazabilidad operativa.

## Hallazgos que cierra

```text
H-009
```

---

# Dependencias

Orden obligatorio:

```text
IMPL-005
↓
IMPL-006
↓
IMPL-010
↓
IMPL-011
```

Razón:

```text
SESSION_SECURE_COOKIE requiere HTTPS operativo.
```

---

# Exclusiones

No incluye:

* Nuevas funcionalidades.
* Cambios de negocio.
* Cambios en módulos Inventario.
* Cambios en módulo User.
* Refactorizaciones de CrudGenerator.

---

# Criterios de Éxito

## Seguridad

* HTTPS operativo.
* HTTP redireccionado.
* Cookies seguras activadas.

---

## Correo

* SMTP funcional.
* Prueba de envío exitosa.

---

## Infraestructura

* Versiones PHP alineadas.
* Política de backups implementada.
* Uso de disco bajo control.

---

## Operación

* Hallazgos H-001 a H-009 cerrados o aceptados formalmente.

---

# Riesgos

## R-01

Fallo de configuración HTTPS.

Mitigación:

```text
Respaldo previo de configuración HestiaCP.
```

---

## R-02

Interrupción temporal de correo.

Mitigación:

```text
Pruebas en ventana controlada.
```

---

## R-03

Cambios de versión PHP.

Mitigación:

```text
Validación Laravel + módulos antes de aplicar.
```

---

# Resultado Esperado

```text
AUDIT-006
↓
PLAN-IMPL-010
↓
IMPL-005
↓
IMPL-006
↓
IMPL-010
↓
IMPL-011
```

permitirá elevar BhagamAppsModular a un estado de producción alineado con buenas prácticas de seguridad, disponibilidad y operación.

---

# Estado Final

```text
PLAN-IMPL-010

Production Security and Infrastructure Hardening

Estado: APROBADO
Resultado esperado: Ejecución por fases
Riesgo residual esperado: BAJO
```

## Documentos Relacionados

* AUDIT-006
* DG-014
* IMPL-005
* IMPL-006
* IMPL-009
* IMPL-010
* IMPL-011
* BASELINE-001
* ROADMAP-001
* PMP-001
