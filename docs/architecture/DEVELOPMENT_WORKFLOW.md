# DEVELOPMENT_WORKFLOW — BhagamApps Modular

**Versión:** 1.0.0
**Fecha:** 2026-06-08
**Estado:** ACTIVO — política obligatoria del proyecto
**ADR de referencia:** ADR-WORKFLOW-001

---

## Principio fundamental

Una tarea NO se considera terminada hasta que:

1. El commit existe en el repositorio local.
2. El push fue exitoso.
3. GitHub está sincronizado con el servidor de producción.

---

## Flujo obligatorio

```
PLAN → IMPL → AUDIT → CHANGELOG → VERSIONING → COMMIT → PUSH
```

Cada etapa puede omitirse únicamente si no aplica a la tarea en curso,
pero la justificación debe quedar registrada.

---

## Etapas del flujo

### PLAN

- Definir el alcance antes de tocar código.
- Para tareas que afectan BD, seguridad o arquitectura: documentar el plan antes de ejecutar.
- Para tareas menores (fix de UI, ajuste de docs): el plan puede ser implícito.

### IMPL

- Ejecutar los cambios según el plan.
- Generar `docs/impl/IMPL-XXX-*.md` para toda implementación que afecte:
  - código funcional
  - migraciones de BD
  - configuración de seguridad
  - arquitectura del sistema
- Nombrar secuencialmente: `IMPL-001`, `IMPL-002`, etc.

### AUDIT

- Verificar que la implementación cumplió su objetivo.
- Generar `docs/audits/AUDIT-XXX-*.md` para:
  - correcciones de seguridad
  - cambios en datos de producción
  - análisis de estado del sistema
- Nombrar secuencialmente: `AUDIT-001`, `AUDIT-002`, etc.

### CHANGELOG

Actualizar obligatoriamente al cierre de cada tarea:

| Archivo | Cuándo actualizar |
|---|---|
| `docs/changelog/<modulo>.md` | Si cambió ese módulo específico |
| `docs/changelog/bhagamapps.md` | Si el cambio es transversal o de plataforma |
| `CHANGELOG.md` (raíz) | En cada nueva versión de BhagamApps |

### VERSIONING

Actualizar cuando el cambio amerite incremento de versión:

| Archivo | Qué actualiza |
|---|---|
| `config/versiones.php` | Fuente de verdad para la UI |
| `VERSIONING.md` | Tabla maestra de versiones |

Reglas SemVer:
- **MAJOR** (`X.0.0`): cambio arquitectónico, ruptura de compatibilidad
- **MINOR** (`x.Y.0`): nueva funcionalidad, nuevo módulo, nuevo comportamiento
- **PATCH** (`x.y.Z`): bug fix, corrección de datos, ajuste de seguridad acotado

### COMMIT

Mensajes semánticos obligatorios:

```
feat(scope):     nueva funcionalidad
fix(scope):      corrección de bug
security(scope): corrección de seguridad
refactor(scope): refactor sin cambio funcional
docs(scope):     documentación únicamente
chore(scope):    mantenimiento, dependencias, configuración
perf(scope):     mejora de rendimiento
test(scope):     tests únicamente
```

`scope` = módulo afectado: `user`, `inventario`, `apps`, `crudgenerator`, `core`, `docs`, `git`, `workflow`

### PUSH

Push obligatorio al finalizar cada sesión de trabajo con cambios.
No acumular commits sin push entre sesiones.

---

## Verificaciones pre-push (obligatorias)

```bash
git status                  # debe mostrar "nothing to commit" o solo lo esperado
git branch --show-current   # debe ser: main
git log --oneline -1        # confirmar el commit a publicar
```

## Verificaciones post-push

Mostrar al finalizar:

- SHA publicado
- Rama publicada
- Estado: `origin/main` actualizado

---

## Operaciones que requieren autorización explícita

Las siguientes operaciones son **destructivas** y requieren confirmación explícita
del usuario antes de ejecutarse, independientemente del contexto:

| Operación | Razón |
|---|---|
| `git push --force` | Reescribe historia remota |
| `git reset --hard` | Descarta cambios irreversiblemente |
| `git rebase` | Reescribe historia local |
| Eliminación de ramas | Puede perder commits |
| Eliminación de tags | Pérdida de referencia histórica |
| `git clean -f` | Elimina archivos no rastreados |

---

## Operaciones autorizadas automáticamente

Salvo instrucción contraria explícita, se consideran autorizadas sin necesidad
de confirmación adicional:

- `git add`
- `git commit`
- `git push` (push normal, no force)
- Creación de tags anotados
- Actualización de `CHANGELOG.md`
- Actualización de `VERSIONING.md`
- Actualización de `config/versiones.php`
- Creación o actualización de documentos en `docs/`

---

## Informe obligatorio de cierre

Al finalizar una tarea, generar siempre:

```markdown
# CIERRE DE IMPLEMENTACIÓN — [IMPL/AUDIT/TASK ID]

## Commits creados
[SHA] [mensaje]

## Archivos modificados
[lista]

## CHANGELOG actualizado
[módulos afectados]

## VERSIONING actualizado
[versiones modificadas, si aplica]

## Push realizado
[rama] → [SHA en GitHub]

## SHA final publicado
[SHA completo]

## Estado Git
On branch main — nothing to commit, working tree clean

## Estado GitHub
origin/main = [SHA]
```

---

## Estado de cumplimiento

| Condición | Estado |
|---|---|
| Commit existe | ✅ / ❌ |
| Push realizado | ✅ / ❌ |
| GitHub sincronizado | ✅ / ❌ |
| CHANGELOG actualizado | ✅ / ❌ / N/A |
| VERSIONING actualizado | ✅ / ❌ / N/A |

Si cualquier condición esencial (commit, push) es ❌:

```
ESTADO = INCOMPLETO
```

Y debe informarse explícitamente al finalizar la sesión.

---

## Fuente oficial de verdad

**GitHub es la fuente oficial del proyecto.**

El servidor de producción es el origen de los cambios, pero GitHub es la fuente
de referencia. Cualquier cambio que exista solo en el servidor y no en GitHub
representa una deuda de sincronización que debe resolverse antes de la siguiente
sesión de trabajo.

---

## Referencia rápida

```
Inicio de sesión:
  git log --oneline -3          # ¿dónde quedé?
  git status                    # ¿hay cambios sin commit?

Durante la tarea:
  Código → Tests → Docs → CHANGELOG → VERSIONING

Cierre de tarea:
  git add <archivos específicos>
  git commit -m "tipo(scope): descripción"
  git push origin main
  → Generar informe de cierre

Operaciones destructivas:
  → Solicitar autorización explícita siempre
```
