# VERSIONING — BhagamApps Modular

Documento oficial de la estrategia de versionado de la plataforma BhagamApps.

---

## Versiones actuales

| Componente     | Versión | Última actualización | Changelog                                    |
|----------------|---------|----------------------|----------------------------------------------|
| **BhagamApps** | v1.9.0  | 2026-06-09           | [`docs/changelog/bhagamapps.md`](docs/changelog/bhagamapps.md) |
| Inventario     | v2.7.0  | 2026-06-09           | [`docs/changelog/inventario.md`](docs/changelog/inventario.md) |
| User           | v2.2.1  | 2026-06-08           | [`docs/changelog/user.md`](docs/changelog/user.md)             |
| Apps           | v1.5.0  | 2026-06-09           | [`docs/changelog/apps.md`](docs/changelog/apps.md)             |
| CrudGenerator  | v1.1.0  | 2025-06-23           | [`docs/changelog/crudgenerator.md`](docs/changelog/crudgenerator.md) |

---

## Versionado de plataforma (BhagamApps)

La versión global de BhagamApps refleja el estado de la plataforma como un todo.
Se incrementa cuando:

| Tipo    | Cuándo incrementar                                                                 |
|---------|------------------------------------------------------------------------------------|
| **Major** (`X.0.0`) | Cambio de arquitectura fundamental, migración de stack, restructuración de módulos |
| **Minor** (`x.Y.0`) | Nueva funcionalidad transversal, nuevo módulo instalado, cambio de seguridad relevante |
| **Patch** (`x.y.Z`) | Correcciones de bugs del core, hotfixes de producción, documentación crítica |

La versión de plataforma **no necesariamente coincide** con la de ningún módulo.
Un módulo puede estar en v2.3.0 mientras la plataforma está en v1.3.0.

---

## Versionado de módulos (independiente)

Cada módulo evoluciona de forma independiente. Sus versiones solo cambian cuando
ese módulo específico recibe cambios.

| Tipo    | Cuándo incrementar                                                                 |
|---------|------------------------------------------------------------------------------------|
| **Major** (`X.0.0`) | Rediseño de la interfaz del módulo, cambios que rompen compatibilidad de datos, migración destructiva |
| **Minor** (`x.Y.0`) | Nueva funcionalidad dentro del módulo, nueva sección o flujo, cambio de comportamiento significativo |
| **Patch** (`x.y.Z`) | Bug fix, corrección de validación, ajuste de UI menor, fix de seguridad acotado al módulo |

### Regla de versión por sesión de trabajo

Al finalizar una sesión de trabajo:
1. Identificar qué módulos recibieron cambios.
2. Incrementar la versión correspondiente según el tipo de cambio.
3. Actualizar la tabla de versiones actuales en este archivo.
4. Registrar el cambio en `docs/changelog/<modulo>.md` con la nueva versión.
5. Si el cambio es transversal, también registrarlo en `docs/changelog/bhagamapps.md`.

---

## Configuración centralizada (ver ADR-004)

La versión de referencia para la interfaz de usuario está definida en:

```
config/versiones.php
```

Este archivo es la **fuente de verdad** para versiones mostradas al usuario.
Los changelogs son la fuente de verdad para el historial de cambios.

> **Nota:** La propuesta original de ADR-004 referenciaba `config/modules.php`.
> Ese archivo corresponde a la configuración del framework `nwidart/laravel-modules`.
> La implementación real usa `config/versiones.php`. ADR-004 fue actualizado en
> IMPL-008 (2026-06-08) para reflejar esta realidad.

Ver `docs/architecture/MODULE_VERSIONING_UI.md` para la especificación de la
UI de visualización de versiones.

Ver `docs/adr/ADR-004-Modular-Versioning.md` para la justificación arquitectónica.

---

## Cambios que NO ameritan incremento de versión de módulo

- Correcciones de documentación (comentarios en código, README, este archivo).
- Cambios en archivos de configuración de desarrollo (`.env.example`, `vite.config.js`).
- Cambios en seeders sin impacto en producción.
- Cambios en tests únicamente.

---

## Changelog del core vs. changelog del módulo

| Tipo de cambio | ¿Dónde registrar? |
|---|---|
| Afecta a un módulo | `docs/changelog/<modulo>.md` |
| Afecta a varios módulos | `docs/changelog/bhagamapps.md` + cada módulo afectado |
| Afecta al core (`app/`, `bootstrap/`, `config/`, `resources/views/auth/`) | `docs/changelog/bhagamapps.md` |
| Corrección en lógica de un módulo | Solo en `docs/changelog/<modulo>.md` |
