# ADR-004 — Dominio Inventario

| Campo | Valor |
|-------|-------|
| **ID** | ADR-004 |
| **Título** | Dominio Inventario: Alcance, Entidades, Responsabilidades y Límites |
| **Estado** | Aprobado |
| **Fecha** | 2026-06-14 |
| **Autores** | Equipo APPSisGOE |
| **Revisores** | — |
| **Documentos base** | AUDIT-INV-002-APPSisGOE.md · ARCH-ANALYSIS-001-APPSisGOE.md |
| **Depende de** | ADR-001 (Core Mínimo) · ADR-002 (Gobernanza de Módulos) · ADR-003 (Autorización) |

---

## 1. Estado

**Aprobado.** Esta decisión define oficialmente el alcance, las entidades, los flujos y los límites del módulo Inventario en APPSisGOE.

---

## 2. Contexto

El módulo Inventario de BhagamAppsModular fue auditado en AUDIT-INV-002-APPSisGOE.md. La auditoría documentó:

- **10 procesos de negocio** (registro, HMB, HEB, custodios, ubicaciones, mantenimientos, actas, catálogos, dashboard, notificaciones)
- **16 tablas** de base de datos
- **22 componentes Livewire**
- **17 rutas HTTP**
- Dos flujos de aprobación: HMB (modificaciones) y HEB (bajas)
- Una brecha funcional: HEB no tiene UI de aprobación para roles no-admin

El módulo Inventario es el más complejo de BhagamApps y el primero en ser migrado a APPSisGOE como módulo de negocio. Esta ADR formaliza qué pertenece al módulo, qué usa del CORE, y cómo se diseñan los flujos más críticos.

**Evidencia de AUDIT-INV-002 §10.1:**
> "Los workflows HMB y HEB son un hallazgo de alto valor. El patrón de 'propuesta → aprobación → aplicación' es un requerimiento de auditoría interna en IEE."

---

## 3. Problema

Sin una definición formal del dominio Inventario:

1. No está claro qué tablas, servicios y componentes pertenecen al módulo vs. al CORE
2. La brecha de HEB (flujo incompleto) puede heredarse a APPSisGOE
3. Los patrones de aprobación pueden implementarse de forma inconsistente en diferentes módulos
4. Los catálogos maestros pueden acoplarse entre módulos
5. No existe criterio para decidir si un nuevo requerimiento pertenece a Inventario o a un servicio compartido

---

## 4. Alternativas consideradas

### Alternativa A — Inventario como módulo monolítico (como BhagamApps)

Todo lo relacionado con bienes, incluyendo ActivityLogger y el sistema de notificaciones, dentro del módulo.

**Rechazada porque:** ActivityLogger y Notificaciones ya fueron formalizados como CORE (ADR-001). Duplicar estas capacidades dentro de Inventario crea acoplamiento y viola el contrato del CORE.

### Alternativa B — Inventario fragmentado en sub-módulos

Separar Inventario en: Bienes, Catálogos, Dashboard, Mantenimientos, Actas.

**Rechazada porque:** Las entidades están fuertemente acopladas (bienes ↔ catálogos ↔ responsables ↔ historial). La separación artificial generaría más complejidad de la que resolvería. El patrón HMB cruza bienes y detalles en una sola transacción. (AUDIT-INV-002 §4.4)

### Alternativa C — Inventario como módulo de negocio cohesivo con límites claros (decisión adoptada)

Inventario es un módulo de negocio que gestiona el ciclo de vida de bienes institucionales. Usa el CORE para autenticación, permisos, auditoría y notificaciones. No exporta sus entidades a otros módulos.

---

## 5. Decisión

### 5.1 Alcance oficial del módulo

**El módulo Inventario gestiona el ciclo de vida completo de bienes muebles institucionales**, desde su alta hasta su baja controlada, incluyendo trazabilidad de cambios, custodios, ubicaciones y mantenimientos.

**Está dentro del alcance:**
- Alta, edición y baja de bienes muebles
- Flujo de aprobación de modificaciones (HMB) — completo
- Flujo de aprobación de bajas (HEB) — completo (incluyendo la UI que falta en BhagamApps)
- Gestión de custodios (cadena de custodia temporal)
- Historial de ubicaciones físicas
- Mantenimientos programados
- Actas de entrega impresas / PDF
- Catálogos maestros propios del dominio
- Dashboard ejecutivo de métricas

**Está fuera del alcance:**
- Autenticación y autorización → CORE-1, CORE-2
- Log de actividad (`ActivityLogger`) → CORE-4
- Sistema de notificaciones (`NotificacionesDropdown`) → CORE-5
- Gestión de usuarios (crear, editar usuarios) → Módulo User
- Gestión de dependencias como unidades organizacionales → si otro módulo las necesita, es un requerimiento de extracción a servicio compartido (ver §5.9)

---

### 5.2 Modelo de datos (16 tablas)

#### Entidad central

| Tabla | Descripción |
|-------|-------------|
| `bienes` | Entidad central. Bien mueble con `SoftDeletes`. |
| `detalles` | Especificaciones técnicas 1:1 con `bienes` |

#### Extensiones del bien

| Tabla | Descripción |
|-------|-------------|
| `bienes_imagenes` | Galería fotográfica (1:N) |
| `bienes_responsables` | Cadena de custodia temporal (`fecha_retiro IS NULL` = activo) |
| `mantenimientos_programados` | Agenda de mantenimientos con estado triestado |

#### Historiales (auditoría de dominio, inmutables)

| Tabla | Descripción |
|-------|-------------|
| `historial_modificaciones_bienes` | Workflow HMB: propuestas de cambio con estado |
| `historial_eliminaciones_bienes` | Workflow HEB: solicitudes de baja con estado |
| `historial_dependencias_bienes` | Registro de traslados entre dependencias (se crea al aprobar HMB de `dependencia_id`) |
| `historial_ubicaciones_bienes` | Registro de cambios de ubicación física |

#### Catálogos maestros

| Tabla | Descripción |
|-------|-------------|
| `categorias` | Clasificación de bienes (con campo `slug` para identificación estable) |
| `dependencias` | Unidades administrativas (nombre, ubicacion_id, user_id del coordinador) |
| `ubicaciones` | Espacios físicos del plantel |
| `estados` | Condición física del bien (Nuevo, Bueno, Regular, Malo) |
| `almacenamientos` | Tipo de almacenamiento |
| `mantenimientos` | Catálogo de tipos de mantenimiento |
| `origenes` | Procedencia del bien (normalizado, sin campo varchar legacy) |

**Total: 16 tablas** — todas con FK al esquema de bienes, sin FK hacia módulos externos (excepto `dependencias.user_id` → `users.id`, que es una dependencia legítima hacia el CORE).

---

### 5.3 Entidades principales y sus responsabilidades

#### Bien (`bienes`)

Es la entidad raíz del dominio. Responsabilidades:
- Mantener el estado actual de todos los atributos del bien
- Registrar la referencia a su categoría, estado, almacenamiento, dependencia y origen
- Proporcionar `SoftDeletes` (los bienes dados de baja son consultables)
- Exponer métodos de consulta: `tieneModificacionesPendientes()`, `camposPendientes()`, `getDisplayValue($campo)`
- Relaciones: `detalle`, `responsableActual`, `imagenes`, `historialModificaciones`, `historialUbicaciones`, `mantenimientosProgramados`

**Decisión sobre campos legacy:**
- `origen` (varchar) debe eliminarse. Solo `origen_id` (FK) es válido en APPSisGOE
- `mantenimiento_id` (FK directa en el bien) debe eliminarse. Los mantenimientos se gestionan en `mantenimientos_programados`

#### Detalle (`detalles`)

Especificaciones técnicas opcionales 1:1 con el bien. Responsabilidades:
- Mantener: `marca`, `color`, `material`, `tamano`, `car_especial`, `otra`
- Se crea solo cuando hay datos técnicos relevantes
- Sus campos participan en el workflow HMB (con `tipo_objeto = 'detalle'`, `valor_nuevo` serializado como JSON)

#### Catálogos maestros

Entidades de soporte que normalizan los valores de los atributos de los bienes. Cada catálogo es un CRUD simple gestionado por coordinadores y administradores.

**Decisión crítica sobre categorías:** El campo `id` de categorías NO debe usarse para identificar grupos semánticos en el código. Se agrega un campo `slug` a `categorias` para referenciar grupos institucionales (Mobiliario, TIC, Audiovisual, etc.) de forma estable.

---

### 5.4 Flujo HMB — Historial de Modificaciones de Bienes

**Propósito:** Ningún usuario de rol básico puede modificar directamente los atributos de un bien. Toda modificación pasa por un workflow de aprobación.

**Estado de la máquina:**

```
[Bien con campo X = valor_actual]
    │
    │ Usuario propone cambio
    ▼
[historial_modificaciones_bienes]
    estado = 'pendiente'
    campo = 'estado_id' (o cualquier campo)
    valor_anterior = valor_actual
    valor_nuevo = valor_propuesto
    tipo_objeto = 'bien' | 'detalle'
    │
    │ Notificación enviada a Administrador y Rectoría
    ▼
[Pendiente — visible en HmbIndex]
    │
    ├── Admin/Rectoría APRUEBA ──► DB::transaction {
    │                                  $bien->campo = valor_nuevo;
    │                                  $bien->save();
    │                                  $modificacion->estado = 'aprobada';
    │                                  if (campo === 'dependencia_id') {
    │                                      HistorialDependenciaBien::create(...)
    │                                  }
    │                              }
    │                              ActivityLogger::log(...)
    │
    └── Admin/Rectoría RECHAZA ──► $modificacion->estado = 'rechazada';
                                    // bien no se modifica
```

**Regla de excepción (Admin/Rectoría):** Los usuarios con rol Administrador o Rectoría pueden modificar campos directamente, sin crear propuesta. El componente `EditarCampoBien` bifurca el comportamiento según el rol.

**Transaccionalidad:** La aprobación es atómica. Si falla la actualización del bien, el historial permanece en `pendiente`.

**Regla de campo pendiente:** Un bien con modificaciones pendientes para un campo no puede recibir nuevas propuestas para el mismo campo hasta que la pendiente sea resuelta. El método `camposPendientes()` del modelo `Bien` provee esta consulta.

---

### 5.5 Flujo HEB — Historial de Eliminaciones de Bienes

**Propósito:** La baja de un bien institucional nunca es inmediata para roles básicos. Los bienes se marcan como "dados de baja" mediante soft delete, solo tras aprobación.

**Estado de la máquina:**

```
[Bien activo]
    │
    │ Usuario solicita baja (con motivo)
    ▼
¿Rol es Administrador o Rectoría?
    │
    ├── SÍ → HistorialEliminacionBien(estado='aprobado')
    │         $bien->delete() [SoftDelete]
    │         ActivityLogger::log('eliminar', ...)
    │
    └── NO → Verificar:
              a. No existe solicitud pendiente para este bien
              b. Usuario pertenece a la dependencia del bien
              HistorialEliminacionBien(estado='pendiente')
              Notificación a Administradores y Rectoría
              │
              ▼
         [Pendiente — visible en HebIndex]
              │
              ├── Admin/Rectoría APRUEBA ──► DB::transaction {
              │                                  $bien->delete() [SoftDelete]
              │                                  $solicitud->estado = 'aprobado';
              │                              }
              │                              ActivityLogger::log(...)
              │
              └── Admin/Rectoría RECHAZA ──► $solicitud->estado = 'rechazado';
                                              // bien permanece activo
```

**Corrección de brecha de BhagamApps:** El `HebController` de BhagamApps solo lista solicitudes — no tiene acciones de aprobar/rechazar. APPSisGOE implementa `HebIndex` Livewire con los métodos `aprobarBaja($id)` y `rechazarBaja($id)`, equivalentes a `HmbIndex::aprobarModificacion()`. Esta es una brecha crítica que debe resolverse antes del lanzamiento del módulo.

**Los bienes dados de baja son consultables** mediante `Bien::onlyTrashed()`. La baja nunca destruye datos.

---

### 5.6 Cadena de custodia (`bienes_responsables`)

**Modelo:** Un bien puede tener múltiples responsables a lo largo del tiempo. El responsable actual es el registro con `fecha_retiro IS NULL`.

**Regla de asignación (transacción obligatoria):**

```php
DB::transaction(function () use ($bienId, $nuevoResponsableId) {
    // Cierra el responsable anterior
    BienResponsable::where('bien_id', $bienId)
        ->whereNull('fecha_retiro')
        ->update(['fecha_retiro' => today()]);

    // Abre el nuevo responsable
    BienResponsable::create([
        'bien_id'          => $bienId,
        'user_id'          => $nuevoResponsableId,
        'fecha_asignacion' => today(),
    ]);
});
```

**Esta transacción es atómica.** No puede existir un período donde un bien no tenga responsable activo entre el cierre del anterior y la apertura del nuevo.

---

### 5.7 Dashboard ejecutivo

El `InventarioDashboard` carga todas las métricas en `mount()`. Grupos de métricas:

| Grupo | Tipo | Consulta |
|-------|------|---------|
| KPIs principales (totales) | Contadores | Bien::count(), Dependencia::count(), bienes_responsables activos |
| Estado ejecutivo | Porcentajes | Activos%, dados de baja%, en mantenimiento% |
| Calidad de datos | Índice completitud | % bienes con cada campo obligatorio no nulo |
| Distribución por categoría | Chart.js Doughnut | GROUP BY categoria_id |
| Top 10 dependencias | Chart.js Bar | GROUP BY dependencia_id LIMIT 10 |
| Condición física | Chart.js Doughnut | GROUP BY estado_id |
| Bienes estratégicos TIC | Contadores por tipo | Filtro por keyword en nombre |
| Grupos institucionales | Contadores | Filtro por slug de categoría (no por ID) |
| Alertas | Lista priorizada | Mantenimientos vencidos, bienes sin responsable, info incompleta |
| Últimos movimientos | Tabs | HMB recientes, ubicaciones recientes, eliminaciones recientes |

**Corrección de BhagamApps:** Los grupos institucionales usan el campo `slug` de `categorias`, no IDs hardcodeados.

---

### 5.8 Capacidades del módulo

El módulo Inventario declara las siguientes capacidades en su `module.json`. Todas deben estar en el enum `Capacidad` del CORE.

**Bienes:** `ver-bienes`, `crear-bienes`, `editar-bienes`, `eliminar-bienes`, `aprobar-bienes`, `ver-historial-bienes`, `ver-imagenes-de-bienes`, `asignar-responsables-a-bienes`

**Aprobaciones:** `gestionar-historial-modificaciones-bienes`, `gestionar-historial-eliminaciones-bienes`, `aprobar-pendientes-bienes`, `editar-aprobaciones-pendientes-bienes`, `eliminar-aprobaciones-pendientes-bienes`

**Catálogos:** CRUD completo para las 7 tablas de catálogo (`ver/crear/editar/eliminar` × `categorias/dependencias/ubicaciones/estados/almacenamientos/mantenimientos/origenes`)

**Responsables:** `ver-responsables-bienes`, `asignar-responsables-bienes`, `editar-responsables-bienes`, `transferir-responsables-bienes`

**Ubicaciones:** `cambiar-ubicacion-bienes`, `ver-historial-ubicaciones-bienes`

**Mantenimientos:** `ver-mantenimientos-programados`, `crear-mantenimientos-programados`, `editar-mantenimientos-programados`, `cancelar-mantenimientos-programados`

**Actas:** `ver-actas-de-entrega`

---

### 5.9 Límites del dominio y servicios compartidos

**Lo que el módulo Inventario exporta (contratos hacia el CORE):**

| Contrato | Descripción |
|---------|-------------|
| `ActivityLogger::log(...)` consumido | El módulo usa el servicio del CORE-4, no lo implementa |
| `Notification::send(...)` consumido | El módulo emite notificaciones usando el CORE-5 |
| `modulo.access:inventario` | Toda ruta del módulo usa este middleware del CORE-3 |

**Lo que el módulo Inventario NO exporta:**

- El modelo `Bien` no debe importarse en otros módulos. Si otro módulo necesita datos de bienes, se define una API de consulta explícita.
- El modelo `Dependencia` tiene una relación con `users` (`user_id`). Si el módulo User u otro módulo necesita listar dependencias de un usuario, debe hacerlo mediante consulta al módulo Inventario, no importando el modelo directamente.
- Los catálogos (`Categoria`, `Ubicacion`, etc.) son privados al módulo Inventario.

**Criterio para extraer un servicio compartido:**

El patrón HMB (propuesta → aprobación → aplicación) puede convertirse en un `ApprovalWorkflowService` compartido **únicamente cuando un segundo módulo requiera el mismo patrón**. En APPSisGOE v1, permanece en Inventario. La extracción se decide en una ADR nueva cuando se detecte la necesidad.

---

### 5.10 Componentes Livewire principales

| Componente | Responsabilidad | Límite de tamaño |
|-----------|----------------|-----------------|
| `BienesIndex` | Listado con CRUD, filtros facetados, soft delete | ≤ 300 líneas (delegar a Actions) |
| `EditarCampoBien` | Edición inline + HMB para roles básicos | ≤ 150 líneas |
| `EditarDetalleBien` | Edición de detalles técnicos + HMB | ≤ 150 líneas |
| `HmbIndex` | Listado y aprobación/rechazo de modificaciones pendientes | ≤ 200 líneas |
| `HebIndex` | Listado y aprobación/rechazo de bajas pendientes (nuevo) | ≤ 200 líneas |
| `InventarioDashboard` | Dashboard ejecutivo completo | ≤ 400 líneas |
| `ActaEntregaIndex` | Generación de actas por responsable | ≤ 200 líneas |

**Referencia a BhagamApps:** `BienesIndex` tenía 668 líneas en BhagamApps (AUDIT-INV-002 §7.2). En APPSisGOE, la lógica de negocio se extrae a Actions siguiendo el patrón Clean Architecture ya establecido.

---

## 6. Consecuencias

### Positivas
- El módulo Inventario tiene límites explícitos — no puede acoplarse accidentalmente a otros módulos
- La brecha HEB se corrige por diseño, no como parche posterior
- Los flujos HMB y HEB son formalmente documentados como requerimientos de auditoría interna
- Los catálogos maestros usan `slug` — eliminando la fragilidad de los IDs hardcodeados
- Los campos legacy de BhagamApps (`origen` varchar, `mantenimiento_id`) no existen en APPSisGOE

### Negativas / Trade-offs
- La tabla `dependencias` tiene `user_id` (FK a `users`) — el módulo Inventario tiene una dependencia legítima hacia el CORE-1 (Users). Esto es aceptable porque users es CORE, no un módulo de negocio
- Los catálogos maestros son privados — si otro módulo necesita "dependencias", no puede importar el modelo. Este trade-off favorece el desacoplamiento sobre la conveniencia

### Restricciones que impone esta decisión
- **HEB debe tener UI de aprobación completa** antes del lanzamiento del módulo. No es opcional.
- **`origen` varchar no debe existir en APPSisGOE.** Solo `origen_id` FK.
- **`mantenimiento_id` no debe existir en `bienes`.** Los mantenimientos se gestionan en `mantenimientos_programados`.
- **Los grupos institucionales usan `categorias.slug`, no IDs.**
- **La cadena de custodia es transaccional.** No puede asignarse un responsable sin cerrar el anterior en la misma transacción.
- **Los modelos de Inventario no son importables por otros módulos** sin una ADR que formalice esa interfaz.

---

## 7. Riesgos

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|-------------|---------|------------|
| HEB sigue sin UI de aprobación en APPSisGOE | Media | Alto | HebIndex Livewire es un entregable obligatorio en Fase 3 (ADR-001 §8 / ARCH-ANALYSIS-001 §8) |
| `BienesIndex` supera 300 líneas por acumulación | Alta | Medio | Code review; límite de tamaño en esta ADR como norma |
| Otro módulo importa `Bien` o `Dependencia` directamente | Media | Medio | Code review; grep en CI: `use Modules\\Inventario\\` en otros módulos es un warning |
| IDs de categorías hardcodeados en código nuevo | Alta | Bajo | Lint rule o code review; el campo `slug` en `categorias` elimina la necesidad |
| Pérdida de datos por soft delete mal aplicado | Muy baja | Muy alto | Tests de invariante: `Bien::find($id)` no retorna bien eliminado; `Bien::onlyTrashed()->find($id)` sí |

---

## 8. Justificación basada en evidencia

| Afirmación | Evidencia |
|-----------|-----------|
| HMB y HEB son requerimientos de auditoría interna | AUDIT-INV-002 §10.1: "requerimiento de auditoría interna en IEE (Instituciones de Educación Estatal)". ARCH-ANALYSIS-001 §1.1. |
| HEB tiene una brecha funcional | AUDIT-INV-002 §5.4: "No existe un componente o ruta para que el Administrador revise y apruebe/rechace solicitudes pendientes de HEB." ARCH-ANALYSIS-001 ERROR-005. |
| La cadena de custodia requiere transacción | AUDIT-INV-002 §3.4: "Al asignar nuevo responsable, se debe cerrar el registro anterior." ARCH-ANALYSIS-001 §1.2 (mejoras). |
| IDs de categorías no son identificadores estables | AUDIT-INV-002 §6.7: "Los IDs de categorías están hardcodeados en el componente — frágil ante cambios en el seeder." ARCH-ANALYSIS-001 ERROR-007. |
| ActivityLogger pertenece al CORE, no a Inventario | AUDIT-INV-002 §10.4: "APPSisGOE debe preservar este patrón pero moverlo al CORE como servicio transversal." ADR-001 CORE-4. |
| El patrón HMB es extraíble solo cuando haya segundo caso | ARCH-ANALYSIS-001 §3 (tabla): "Mantener en Inventario en APPSisGOE v1. Extraer a servicio compartido solo cuando un segundo módulo necesite el mismo patrón." |
| BienesIndex no puede superar 668 líneas | AUDIT-INV-002 §7.2: "componente más complejo del módulo (668 líneas)". ARCH-ANALYSIS-001 ERROR-004: "Fat Models / Fat Components". |

---

*Decisiones relacionadas: ADR-001 (Core Mínimo) · ADR-002 (Gobernanza de Módulos) · ADR-003 (Autorización)*
