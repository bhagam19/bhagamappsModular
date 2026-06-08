# ADR-005 — Documentation and Repository Governance

## Estado

Aprobado

## Fecha

2026-06-08

## Tipo

Governance ADR

## Relacionado con

* ADR-001 Modular Architecture
* ADR-004 Modular Versioning
* DEVELOPMENT_WORKFLOW.md
* IMPL-GIT-001
* BASELINE-001

---

# Contexto

Durante la auditoría BASELINE-001 se confirmó que BhagamAppsModular dispone de:

* Repositorio Git operativo.
* Sincronización activa con GitHub.
* Arquitectura modular estable.
* Sistema de documentación técnica en crecimiento.

Sin embargo, se identificó la necesidad de formalizar las reglas que gobiernan:

* La creación de documentación.
* El almacenamiento de documentos.
* La trazabilidad de cambios.
* La sincronización obligatoria con GitHub.

Hasta este momento algunas decisiones y documentos existían únicamente en conversaciones entre agentes, generando riesgo de pérdida de conocimiento institucional.

---

# Problema

Sin una política formal:

* Los documentos pueden quedar dispersos.
* Las decisiones pueden perderse.
* El repositorio puede dejar de reflejar el estado real del proyecto.
* Los agentes pueden trabajar con información inconsistente.

Se requiere una única fuente oficial de verdad.

---

# Decisión

BhagamAppsModular adopta el repositorio Git como fuente oficial y única de documentación del proyecto.

Todo documento aprobado deberá almacenarse dentro del repositorio.

Ningún documento oficial existirá exclusivamente en conversaciones, chats o sistemas externos.

---

# Política de Documentación

Se consideran documentos oficiales:

* PMP
* ADR
* DDOM
* PLAN
* IMPL
* AUDIT
* BASELINE
* ROADMAP
* CHANGELOG
* RELEASE

Todo documento oficial deberá:

1. Tener identificador único.
2. Estar almacenado dentro de `/docs`.
3. Estar versionado mediante Git.
4. Permanecer accesible desde el repositorio.

---

# Estructura Documental Oficial

```text
docs/
├── adr/
├── ddom/
├── pmp/
├── plan/
├── impl/
├── audits/
├── architecture/
├── roadmap/
├── releases/
└── changelog/
```

---

# Jerarquía Documental

La documentación deberá seguir el siguiente orden jerárquico:

```text
PMP
│
├── ROADMAP
│
├── ADR
│   └── DDOM
│
├── PLAN
│
├── IMPL
│
├── AUDIT
│
├── CHANGELOG
│
└── RELEASE
```

---

# Política de Trazabilidad

Toda modificación relevante deberá ser rastreable.

El flujo oficial será:

```text
Necesidad
↓
Análisis
↓
ADR o DDOM (si aplica)
↓
PLAN
↓
Implementación
↓
IMPL
↓
Commit
↓
Push
↓
Release
```

---

# Política de GitHub

Todo cambio aprobado deberá finalizar con:

```bash
git add .
git commit
git push
```

No se considerará terminado un trabajo mientras exista únicamente en el entorno local.

---

# Política para Agentes

## Dirección General

Responsable de:

* PMP
* Roadmap
* Gobierno del proyecto
* Priorización

---

## Arquitectura

Responsable de:

* ADR
* DDOM
* Estándares técnicos

---

## Implementación

Responsable de:

* Código
* Migraciones
* PLAN
* IMPL
* Commits
* Push

---

## Auditoría

Responsable de:

* AUDIT
* BASELINE
* Seguridad
* Calidad

---

# Consecuencias

## Positivas

* Trazabilidad completa.
* Historial verificable.
* Menor pérdida de conocimiento.
* Facilita incorporación de nuevos colaboradores.
* Facilita auditorías futuras.

## Negativas

* Incrementa disciplina documental requerida.
* Añade pasos obligatorios antes del cierre de una tarea.

---

# Cumplimiento

A partir de la aprobación de este ADR:

1. Todo documento oficial deberá almacenarse en el repositorio.
2. Todo cambio aprobado deberá finalizar con commit y push.
3. GitHub se considera la fuente oficial del estado del proyecto.
4. Los agentes deberán respetar las responsabilidades definidas en este documento.

---

# Revisión

La vigencia de este ADR será revisada durante la elaboración de PMP-002 o cuando exista una modificación sustancial del flujo de trabajo del proyecto.
