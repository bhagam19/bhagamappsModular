# AUDIT-APPS-006 — Application Registry Data Integrity Audit

**Estado:** EJECUTADA  
**Responsable:** Auditoría (Claude Code)  
**Fecha de ejecución:** 2026-06-09  
**Proyecto:** BhagamAppsModular  
**Repositorio:** `https://github.com/bhagam19/bhagamappsModular.git`  
**Ruta auditada:** `/home/adolfo/web/bhagamapps.com/private/bhagamappsModular`  
**Base de datos:** `adolfo_bhagamappsModular`  
**Rama activa al momento de auditoría:** `main` (commit `141a3de`)  

---

## 1. Resumen Ejecutivo

AUDIT-APPS-006 evaluó la integridad del módulo Apps como Registro Oficial de Aplicaciones
de BhagamAppsModular, verificando duplicados, campos faltantes, huérfanos, permisos
inconsistentes y correspondencia con módulos reales.

**Hallazgos principales:**

| Riesgo | Hallazgos |
|--------|-----------|
| CRÍTICO | 1 (12 registros legacy sin slug en apps) |
| ALTO | 2 (duplicados nombre × 8 + rutas × 9; 20/24 apps sin módulo real) |
| MEDIO | 3 (gap ID 27 en permisos; roles sin permisos; Coordinador con administrar-apps) |
| BAJO | 2 (app_user vacía; desbalance distribución roles-usuarios) |

**Veredicto:** **REQUIERE DEPURACIÓN FINAL** — El catálogo contiene una capa legacy
(IDs 1-12, sin slug, habilitada=0) que duplica nombre y ruta con los registros del sistema
actual (IDs 13-24). Estos registros legacy deben depurarse antes de establecer baseline.
Las tablas RBAC están íntegras y sin huérfanos.

---

## 2. Contexto de la Auditoría

AUDIT-APPS-006 fue precedida por AUDIT-APPS-005B, que identificó registros legacy en la
tabla `apps` con campos vacíos (slug, descripción, icono). Esta auditoría valida la
integridad completa antes de declarar el módulo Apps cerrado.

El módulo `Apps` implementa un catálogo de aplicaciones del sistema con ciclo de vida
(habilitada/deshabilitada), control de acceso por roles (`app_role`) y asignación directa
a usuarios (`app_user`). Hay 4 módulos reales en `Modules/`: `Apps`, `CrudGenerator`,
`Inventario`, `User`.

---

## 3. Alcance Ejecutado

### 3.1 Modelos examinados

| Modelo | Estado |
|--------|--------|
| App (tabla `apps`) | EXISTS — 24 registros |
| Role (tabla `roles`) | EXISTS — 7 registros |
| Permission (tabla `permissions`) | EXISTS — 34 registros |

### 3.2 Tablas examinadas

| Tabla | Estado | Registros |
|-------|--------|-----------|
| `apps` | EXISTS | 24 |
| `app_role` | EXISTS | 16 |
| `app_user` | EXISTS | 0 (vacía) |
| `permissions` | EXISTS | 34 (gap en ID 27) |
| `roles` | EXISTS | 7 |
| `permission_role` | EXISTS | 96 |
| `permission_user` | EXISTS | 0 (vacía) |

---

## 4. Evidencias Recopiladas

### 4.1 Evidencia E-001 — Tabla `apps` completa (24 registros)

**Estructura:**
```
id | nombre             | slug                | ruta                   | habilitada | orden | user_id | created_at
```

**Registros legacy (IDs 1–12): sin slug, user_id=1, habilitada=0, creados 2026-06-07**
```
1  | User                | NULL | /usuarios/user          | 0 | 99
2  | Inventario          | NULL | /inventario/bienes      | 0 | 99
3  | App                 | NULL | /app                    | 0 | 99
4  | Biblioteca          | NULL | /biblioteca             | 0 | 99
5  | SINAI vs SIMAT      | NULL | /SvS                    | 0 | 99
6  | Planeador           | NULL | /planeador              | 0 | 99
7  | EduInclusiva        | NULL | /eduInclusiva           | 0 | 99
8  | CTE                 | NULL | /cte                    | 0 | 99
9  | Creador de Exámenes | NULL | /creadorExamenes        | 0 | 99
10 | Tabletas            | NULL | /prestamoTabletas       | 0 | 99
11 | Polla Mundialista   | NULL | /pollaMundialista       | 0 | 99
12 | Evaluar para Avanzar| NULL | /evaluarParaAvanzar     | 0 | 99
```

**Registros actuales (IDs 13–24): con slug, user_id=NULL, creados 2026-06-08**
```
13 | Aplicaciones        | apps              | /apps/admin        | 0 | 3
14 | crudgenerator       | crudgenerator     | /crudgenerator     | 0 | 99
15 | Inventario          | inventario        | /inventario/bienes | 1 | 2
16 | Usuarios            | user              | /user              | 1 | 1
17 | Biblioteca          | biblioteca        | /biblioteca        | 0 | 4
18 | SINAI vs SIMAT      | sinai-vs-simat    | /SvS               | 0 | 10
19 | Planeador           | planeador         | /planeador         | 0 | 11
20 | EduInclusiva        | edu-inclusiva     | /eduInclusiva      | 0 | 12
21 | CTE                 | cte               | /cte               | 0 | 13
22 | Creador de Exámenes | creador-examenes  | /creadorExamenes   | 0 | 14
23 | Préstamo Tabletas   | prestamo-tabletas | /prestamoTabletas  | 0 | 15
24 | Evaluar para Avanzar| evaluar-para-avanzar | /evaluarParaAvanzar | 0 | 16
```

---

### 4.2 Evidencia E-002 — Tabla `roles` (7 registros)

```
id | nombre        | descripcion                                           | app_id
---+---------------+-------------------------------------------------------+-------
1  | Administrador | Usuario con acceso completo al sistema.               | 1
2  | Rector        | Orienta todos los procesos en general.                | 1
3  | Coordinador   | Supervisa procesos académicos o administrativos.     | 1
4  | Auxiliar      | Apoya todos los procesos académicos o administrativos.| 1
5  | Docente       | Encargado de impartir clases y evaluar a los alumnos. | 1
6  | Estudiante    | Usuario que accede a contenidos y actividades.        | 1
7  | Invitado      | Acceso limitado para pruebas o demostraciones.       | 1
```

Nota: todos los roles tienen `app_id=1` (referencia a la app "User", ID 1).

---

### 4.3 Evidencia E-003 — Tabla `permissions` (34 registros, gap en ID 27)

```
IDs presentes: 1-26, 28-35 (ID 27 AUSENTE)

Categorías:
  usuarios (4):      ver/crear/editar/eliminar-usuarios
  roles    (5):      ver/crear/editar/eliminar-roles + asignar-permisos-a-roles
  permisos (4):      ver/crear/editar/eliminar-permisos
  bienes   (8):      ver/crear/editar/eliminar/aprobar/ver-historial/
                     asignar-responsables/ver-imagenes-de-bienes
  aprobaciones pendientes (4): ver/aprobar/editar/eliminar-aprobaciones-pendientes-bienes
  actas de entrega (1):        ver-actas-de-entrega  [ID 26]
  [ID 27 AUSENTE]
  apps     (5):      administrar-apps, ver-apps, crear-apps, editar-apps, eliminar-apps
  grupos   (1):      ver-grupos
  evaluacion-docente (1): ver-evaluacion-docente
  biblioteca (1):    ver-biblioteca
```

Permiso ID 27 no existe. El rango salta de ID 26 a ID 28.

---

### 4.4 Evidencia E-004 — Tabla `app_role` (16 registros)

```
id | app_id | role_id | Interpretación
---+--------+---------+--------------------------------------------------
1  |  1     |  1      | App legacy "User" → Administrador
2  |  1     |  2      | App legacy "User" → Rector
3  |  2     |  1      | App legacy "Inventario" → Administrador
4  |  2     |  2      | App legacy "Inventario" → Rector
5  |  3     |  1      | App legacy "App" → Administrador
6  |  3     |  2      | App legacy "App" → Rector
7  |  4     |  1      | App legacy "Biblioteca" → Administrador
8  |  4     |  2      | App legacy "Biblioteca" → Rector
9  | 13     |  1      | App "Aplicaciones/apps" → Administrador
10 | 13     |  2      | App "Aplicaciones/apps" → Rector
11 | 15     |  1      | App "Inventario" (actual) → Administrador
12 | 15     |  2      | App "Inventario" (actual) → Rector
13 | 16     |  1      | App "Usuarios" (actual) → Administrador
14 | 16     |  2      | App "Usuarios" (actual) → Rector
15 | 17     |  1      | App "Biblioteca" (actual) → Administrador
16 | 17     |  2      | App "Biblioteca" (actual) → Rector
```

---

### 4.5 Evidencia E-005 — Tabla `app_user` (vacía)

```
Registros: 0
```

Ningún usuario ha sido asignado directamente a ninguna aplicación.

---

### 4.6 Evidencia E-006 — Matriz `permission_role` (96 registros)

| Rol | Permisos asignados | Cobertura |
|-----|--------------------|-----------|
| Administrador (1) | 34 (todos) | Completa |
| Rector (2) | 34 (todos) | Completa |
| Coordinador (3) | 12 | Parcial |
| Auxiliar (4) | 8 | Parcial |
| Docente (5) | 8 | Parcial |
| Estudiante (6) | 0 | **SIN PERMISOS** |
| Invitado (7) | 0 | **SIN PERMISOS** |

Permisos asignados al Coordinador (12):
`ver-bienes, crear-bienes, editar-bienes, administrar-apps, crear-apps, editar-apps,
eliminar-apps, ver-apps, ver-biblioteca, ver-evaluacion-docente, ver-grupos, ver-usuarios`

---

### 4.7 Evidencia E-007 — Tabla `users` (116 usuarios)

```
Distribución por rol:
  Administrador (ID 1): 1 usuario  [ID 54 — Adolfo León Ruiz Hernández]
  Rector        (ID 2): 0 usuarios
  Coordinador   (ID 3): 5 usuarios
  Auxiliar      (ID 4): 28 usuarios
  Docente       (ID 5): 82 usuarios
  Estudiante    (ID 6): 0 usuarios
  Invitado      (ID 7): 0 usuarios

  Usuarios sin role_id: 0
  Usuarios con role_id inexistente: 0
  GAP en IDs usuarios: IDs 71 y 72 ausentes
```

---

### 4.8 Evidencia E-008 — Módulos reales vs tabla `apps`

**`modules_statuses.json`:**
```json
{
  "User": 1,
  "Inventario": 1,
  "Apps": 1,
  "CrudGenerator": 1
}
```

**Correlación `apps` BD ↔ `Modules/`:**
```
App ID=1  nombre=User          slug=NULL       → Módulo User (EXISTE — legacy sin slug)
App ID=2  nombre=Inventario    slug=NULL       → Módulo Inventario (EXISTE — legacy sin slug)
App ID=3  nombre=App           slug=NULL       → SIN módulo (legacy, nombre incorrecto)
App ID=4  nombre=Biblioteca    slug=NULL       → SIN módulo (legacy)
App ID=5  nombre=SINAI vs SIMAT slug=NULL      → SIN módulo (legacy)
App ID=6  nombre=Planeador     slug=NULL       → SIN módulo (legacy)
App ID=7  nombre=EduInclusiva  slug=NULL       → SIN módulo (legacy)
App ID=8  nombre=CTE           slug=NULL       → SIN módulo (legacy)
App ID=9  nombre=Creador de Exámenes slug=NULL → SIN módulo (legacy)
App ID=10 nombre=Tabletas      slug=NULL       → SIN módulo (legacy)
App ID=11 nombre=Polla Mundialista slug=NULL   → SIN módulo (legacy)
App ID=12 nombre=Evaluar para Avanzar slug=NULL→ SIN módulo (legacy)
App ID=13 nombre=Aplicaciones  slug=apps       → Módulo Apps ✓
App ID=14 nombre=crudgenerator slug=crudgen.   → Módulo CrudGenerator ✓
App ID=15 nombre=Inventario    slug=inventario → Módulo Inventario ✓
App ID=16 nombre=Usuarios      slug=user       → Módulo User ✓
App ID=17 nombre=Biblioteca    slug=biblioteca → SIN módulo (planificado)
App ID=18 nombre=SINAI vs SIMAT slug=sinai-..  → SIN módulo (planificado)
App ID=19 nombre=Planeador     slug=planeador  → SIN módulo (planificado)
App ID=20 nombre=EduInclusiva  slug=edu-incl.  → SIN módulo (planificado)
App ID=21 nombre=CTE           slug=cte        → SIN módulo (planificado)
App ID=22 nombre=Creador de Exámenes slug=...  → SIN módulo (planificado)
App ID=23 nombre=Préstamo Tabletas slug=...    → SIN módulo (planificado)
App ID=24 nombre=Evaluar para Avanzar slug=... → SIN módulo (planificado)
```

---

### 4.9 Evidencia E-009 — Análisis de huérfanos

```
app_role con app_id inexistente:    0
app_role con role_id inexistente:   0
permission_role con permission_id inexistente: 0
permission_role con role_id inexistente:       0
Usuarios con role_id inexistente:   0
```

Sin registros huérfanos en ninguna tabla de relaciones.

---

### 4.10 Evidencia E-010 — Rutas activas para apps habilitadas

**Apps con habilitada=1:**
```
ID 15 | Inventario | ruta=/inventario/bienes
  → Ruta confirmada: inventario.bienes.index (Modules\Inventario\...) ✓

ID 16 | Usuarios | ruta=/user
  → Ruta confirmada: user.* (Modules\User\...) + Jetstream/Fortify routes ✓
```

---

## 5. Respuestas P-001 → P-010

### P-001 — ¿Existen aplicaciones duplicadas?

**SÍ — 8 pares de nombres duplicados**

Los registros legacy (IDs 1-12) comparten nombre con los registros actuales (IDs 13-24):

| Nombre duplicado | ID Legacy (sin slug) | ID Actual (con slug) |
|-----------------|---------------------|----------------------|
| Inventario      | 2 | 15 |
| Biblioteca      | 4 | 17 |
| SINAI vs SIMAT  | 5 | 18 |
| Planeador       | 6 | 19 |
| EduInclusiva    | 7 | 20 |
| CTE             | 8 | 21 |
| Creador de Exámenes | 9 | 22 |
| Evaluar para Avanzar | 12 | 24 |

Adicionalmente: "App" (ID 3) y "Aplicaciones" (ID 13) son funcionalmente el mismo módulo
(Apps), con nombres distintos.

**Causa:** Los IDs 1-12 son registros del sistema legacy que no fueron eliminados al
implementarse el catálogo actual.

---

### P-002 — ¿Existen slugs duplicados?

**NO** — Los slugs son únicos entre todos los registros que los poseen.

Los IDs 1-12 tienen `slug = NULL`, por lo que no generan conflicto de duplicidad de slug
pero representan un problema de integridad de datos (ver P-004).

---

### P-003 — ¿Existen rutas duplicadas?

**SÍ — 9 pares de rutas duplicadas**

| Ruta               | App legacy (ID) | App actual (ID) |
|--------------------|-----------------|-----------------|
| /inventario/bienes | 2               | 15              |
| /biblioteca        | 4               | 17              |
| /SvS               | 5               | 18              |
| /planeador         | 6               | 19              |
| /eduInclusiva      | 7               | 20              |
| /cte               | 8               | 21              |
| /creadorExamenes   | 9               | 22              |
| /prestamoTabletas  | 10              | 23              |
| /evaluarParaAvanzar | 12             | 24              |

El duplicado de `/inventario/bienes` y `/user` (equivalente) es especialmente relevante
porque afecta las apps actualmente habilitadas.

---

### P-004 — ¿Existen aplicaciones sin nombre, slug, ruta?

**SÍ — 12 aplicaciones sin slug**

Los registros IDs 1-12 tienen `slug = NULL`. El campo `nombre` y el campo `ruta` están
presentes en todos los registros.

| Campo   | Apps con NULL | IDs afectados |
|---------|---------------|---------------|
| nombre  | 0             | — |
| slug    | 12            | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 |
| ruta    | 0             | — |

---

### P-005 — ¿Existen aplicaciones activas sin permisos asociados?

**NO — Las 2 apps activas tienen cobertura de permisos**

| App activa | Permisos relacionados en sistema |
|------------|----------------------------------|
| Inventario (ID 15) | ver-bienes, crear-bienes, editar-bienes, eliminar-bienes, aprobar-bienes, ver-historial-bienes, asignar-responsables-a-bienes, ver-imagenes-de-bienes, gestionar-historial-modificaciones-bienes, aprobar-pendientes-bienes, editar-aprobaciones-pendientes-bienes, eliminar-aprobaciones-pendientes-bienes, ver-actas-de-entrega |
| Usuarios (ID 16) | ver-usuarios, crear-usuarios, editar-usuarios, eliminar-usuarios, ver-roles, crear-roles, editar-roles, eliminar-roles, asignar-permisos-a-roles, ver-permisos, crear-permisos, editar-permisos, eliminar-permisos |

Nota: no existe relación directa `app ↔ permission` (no hay tabla `app_permission`). La
cobertura se infiere por categoría semántica. El control de acceso está mediado por roles.

---

### P-006 — ¿Existen aplicaciones activas sin roles asociados?

**NO**

| App activa | Roles en app_role |
|------------|-------------------|
| Inventario (ID 15) | Administrador, Rector |
| Usuarios (ID 16) | Administrador, Rector |

Sin embargo: el rol `Rector` (ID 2) tiene **0 usuarios asignados** en la tabla `users`.
Las apps activas tienen roles configurados, pero uno de esos roles no tiene usuarios reales.

---

### P-007 — ¿Existen registros huérfanos en `app_role` / `app_user`?

**NO — Sin huérfanos**

```
app_role con app_id inexistente:  0
app_role con role_id inexistente: 0
app_user: 0 registros totales (sin posibilidad de huérfanos)
```

Observación: la tabla `app_user` está vacía. El mecanismo de asignación de acceso
opera exclusivamente vía roles (tabla `app_role`), no asignaciones directas usuario-app.

---

### P-008 — ¿Existen permisos de acceso faltantes?

**PARCIALMENTE SÍ — 3 sub-hallazgos:**

**A) Gap de ID en `permissions` (ID 27 ausente):**  
Los IDs saltan de 26 (`ver-actas-de-entrega`) a 28 (`administrar-apps`). Un permiso fue
creado y eliminado. Su nombre no es recuperable desde la BD actual.

**B) Roles sin permisos asignados:**  
Los roles `Estudiante` (ID 6) y `Invitado` (ID 7) tienen 0 permisos en `permission_role`.
Cualquier usuario con uno de estos roles no puede acceder a ninguna funcionalidad del sistema.

**C) Permiso `ver-apps` asignado al Coordinador (incongruencia):**  
El Coordinador tiene asignados los permisos `administrar-apps`, `crear-apps`, `editar-apps`,
`eliminar-apps` y `ver-apps`. La capacidad de administrar el catálogo completo de apps por
un Coordinador puede ser una sobreautorización respecto a las responsabilidades del rol.

---

### P-009 — ¿Existen aplicaciones visibles sin rutas válidas?

**NO — Las apps activas tienen rutas funcionales**

```
App Inventario (ID 15) ruta=/inventario/bienes → inventario.bienes.index EXISTE ✓
App Usuarios   (ID 16) ruta=/user              → rutas user.* EXISTEN ✓
```

Observación: las apps con `habilitada=0` (22 de 24) tienen rutas registradas, pero no
se audita su validez al no estar activas. Los IDs 3, 11 (`/app`, `/pollaMundialista`)
no tienen módulos implementados, pero ambas están deshabilitadas.

---

### P-010 — ¿Existen aplicaciones sin módulos reales?

**SÍ — 20 de 24 apps no corresponden a módulos reales implementados**

| Categoría | IDs | Cantidad | Estado |
|-----------|-----|----------|--------|
| Con módulo real (slug correcto) | 13, 14, 15, 16 | 4 | OK |
| Legacy (sin slug, sin módulo) | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 | 12 | Legacy/deuda |
| Planificados (slug, sin módulo) | 17, 18, 19, 20, 21, 22, 23, 24 | 8 | Futuro sin módulo |

Los IDs 1-12 son registros legacy del sistema anterior (sin slug, user_id=1) que
coexisten con los registros actuales, generando duplicados de nombre y ruta.

Los IDs 17-24 son apps planificadas con slug asignado pero sin módulo implementado en
`Modules/`. Están deshabilitadas (habilitada=0), lo cual es correcto, pero generan
duplicados de ruta con los legacys correspondientes (IDs 4-10, 12).

---

## 6. Hallazgos

### H-001 — 12 registros legacy sin slug en tabla `apps`
**Riesgo:** CRÍTICO  
**Descripción:** Los registros IDs 1-12 son del sistema anterior. Tienen `slug=NULL`,
`user_id=1`, `habilitada=0`, `orden=99` y fueron creados el 2026-06-07. No son registros
del catálogo oficial actual pero permanecen en la tabla.  
**Evidencia:** E-001, P-004  
**Impacto:** Generan 8 duplicados de nombre y 9 duplicados de ruta con el catálogo actual.
Cualquier consulta `App::visiblesPara()` o lógica que recorra la tabla sin filtrar slug
puede incluirlos indebidamente. Dificultan auditorías y mantenimiento.  
**Riesgo de integridad:** La tabla no tiene constraint UNIQUE en `ruta`, lo que permite
convivencia de rutas duplicadas.

---

### H-002 — 8 nombres duplicados y 9 rutas duplicadas
**Riesgo:** ALTO  
**Descripción:** 8 pares de apps comparten el mismo nombre. 9 pares comparten la misma
ruta. En todos los casos, uno de los registros es legacy (IDs 1-12) y el otro es actual
(IDs 13-24).  
**Evidencia:** E-001, P-001, P-003  
**Impacto:** Ambigüedad en el catálogo. El par crítico es `/inventario/bienes` duplicado
en IDs 2 (legacy, deshabilitada) y 15 (actual, habilitada=1). Si algún proceso consulta
la app por ruta sin filtro `habilitada=1`, podría retornar el registro incorrecto.

---

### H-003 — 20/24 apps sin módulo real (12 legacy + 8 planificadas)
**Riesgo:** ALTO  
**Descripción:** Solo 4 apps (IDs 13-16) corresponden a módulos reales en `Modules/`.
Las 12 legacy no tienen módulo ni slug. Las 8 planificadas (IDs 17-24) tienen slug pero
no existe el módulo en `Modules/` ni en `modules_statuses.json`.  
**Evidencia:** E-008, P-010  
**Impacto:** El catálogo está "inflado" con entradas que no son operativas. Si se
habilita una de las 8 planificadas sin su módulo, la ruta no funcionará.

---

### H-004 — Gap en IDs de `permissions` (ID 27 ausente)
**Riesgo:** MEDIO  
**Descripción:** El ID 27 no existe en la tabla `permissions`. Un permiso fue creado y
eliminado sin dejar registro documental del nombre eliminado.  
**Evidencia:** E-003, P-008-A  
**Impacto funcional:** Ninguno — los 34 permisos restantes son funcionales. El riesgo
es de trazabilidad: no es posible determinar qué permiso existió en el ID 27.

---

### H-005 — Roles `Estudiante` e `Invitado` sin permisos asignados
**Riesgo:** MEDIO  
**Descripción:** Los roles ID 6 (Estudiante) y ID 7 (Invitado) tienen 0 permisos en
`permission_role`. Están registrados en la tabla `roles` pero son inoperantes.  
**Evidencia:** E-006, P-008-B  
**Impacto:** Los roles existen pero no pueden usarse. Si se asigna un usuario con uno
de estos roles, no podrá acceder a ninguna funcionalidad. No hay usuarios actuales con
estos roles (coherente con el estado).

---

### H-006 — Coordinador con permisos plenos sobre catálogo de apps
**Riesgo:** MEDIO  
**Descripción:** El rol Coordinador tiene asignados `administrar-apps`, `crear-apps`,
`editar-apps`, `eliminar-apps` y `ver-apps`. Tiene capacidad de gestión completa del
catálogo de aplicaciones. La pertinencia de esta asignación depende del diseño de roles,
pero puede representar una sobreautorización.  
**Evidencia:** E-006, P-008-C  
**Impacto:** Un Coordinador puede habilitar/deshabilitar apps, crearlas y eliminarlas.
Si no es intencional, representa un riesgo de autorización.

---

### H-007 — Tabla `app_user` vacía
**Riesgo:** BAJO  
**Descripción:** La tabla `app_user` existe con schema correcto pero contiene 0 registros.
El mecanismo de asignación directa usuario-app no se está usando.  
**Evidencia:** E-005  
**Impacto:** Sin impacto operativo actual. Podría ser una característica incompleta o
una decisión de diseño donde el acceso se gestiona solo por roles.

---

### H-008 — Rol `Rector` sin usuarios; desequilibrio distribución
**Riesgo:** BAJO  
**Descripción:** El rol Rector (ID 2) tiene 0 usuarios asignados. De 116 usuarios: 82
son Docentes, 28 Auxiliares, 5 Coordinadores, 1 Administrador. Los roles Rector, Estudiante
e Invitado tienen 0 usuarios.  
**Evidencia:** E-007  
**Impacto:** El rol Rector tiene permisos completos (34/34) pero ningún usuario lo ejerce.
Las apps activas tienen app_role con Rector, pero ese rol no tiene usuarios reales.

---

## 7. Matriz de Hallazgos

| ID    | Hallazgo                                          | Riesgo   | Integridad | Operación | Mantenibilidad |
|-------|---------------------------------------------------|----------|------------|-----------|----------------|
| H-001 | 12 registros legacy sin slug                      | CRÍTICO  | DEGRADADA  | NORMAL    | ALTO IMPACTO   |
| H-002 | 8 nombres + 9 rutas duplicadas                    | ALTO     | DEGRADADA  | RIESGO    | ALTO IMPACTO   |
| H-003 | 20/24 apps sin módulo real                        | ALTO     | DEGRADADA  | RIESGO    | ALTO IMPACTO   |
| H-004 | Gap ID 27 en permissions                          | MEDIO    | DEGRADADA  | NORMAL    | BAJO           |
| H-005 | Roles Estudiante/Invitado sin permisos            | MEDIO    | DEGRADADA  | BLOQUEADA | MEDIO          |
| H-006 | Coordinador con gestión plena de apps             | MEDIO    | OK         | ACTIVA    | MEDIO          |
| H-007 | app_user vacía                                    | BAJO     | OK         | NORMAL    | BAJO           |
| H-008 | Rector sin usuarios / desequilibrio de roles      | BAJO     | OK         | LIMITADA  | BAJO           |

---

## 8. Clasificación Global

```
MÓDULO APPS — BhagamAppsModular
Tablas:  apps ✓, app_role ✓, app_user ✓, permissions ✓, roles ✓
RBAC:    Íntegro, sin huérfanos
Datos:   DEGRADADOS — 12 registros legacy + 8 duplicados de nombre + 9 de ruta

Resultado: REQUIERE DEPURACIÓN FINAL
```

El módulo **NO puede declararse APTO PARA CIERRE** porque:

1. 12 registros legacy (IDs 1-12) contaminan el catálogo con duplicados.
2. 9 rutas duplicadas generan ambigüedad potencial en consultas sin filtro `habilitada`.
3. La integridad declarativa del catálogo no refleja el estado real de los módulos.

La RBAC es íntegra. Las operaciones activas (Inventario, Usuarios) funcionan correctamente.
El bloqueo para cierre es exclusivamente la deuda de datos legacy.

---

## 9. Recomendación Final

### Depuración requerida antes de cierre (DEPURACIÓN FINAL)

**R-001 — Eliminar registros legacy IDs 1-12 (bloqueante):**  
Estos registros son del sistema anterior. Deben eliminarse de la tabla `apps` (y sus
relaciones en `app_role`) antes de establecer baseline. Verificar que ninguna lógica de
negocio activa los referencie por ID hardcodeado.

**R-002 — Confirmar o eliminar apps planificadas sin módulo (IDs 17-24):**  
Las 8 apps planificadas están correctamente deshabilitadas. Si el roadmap las contempla,
pueden mantenerse. Si no hay plan de implementación activo, deben eliminarse para mantener
el catálogo limpio.

### Correcciones necesarias (post-depuración)

**R-003 — Asignar permisos a roles Estudiante e Invitado (H-005):**  
O eliminar estos roles si no se planea usarlos. Roles sin permisos no tienen utilidad
operativa y generan deuda conceptual.

**R-004 — Revisar asignación de permisos de apps a Coordinador (H-006):**  
Validar si es intencional que Coordinador pueda crear/eliminar apps del catálogo.

**R-005 — Documentar permiso eliminado ID 27 (H-004):**  
Registrar en bitácora qué permiso existió en el ID 27 y por qué fue eliminado.

### Sin acción requerida

- La tabla `app_user` vacía no requiere acción — es un estado operacional válido.
- Los roles Rector sin usuarios no requieren acción inmediata — es un estado de carga
  de datos pendiente.
- Las rutas de apps activas son válidas y funcionales.

---

## 10. Estado Final de la Auditoría

```
AUDIT-APPS-006 — Application Registry Data Integrity Audit
Estado: EJECUTADA Y CERRADA
Fecha: 2026-06-09
Resultado: REQUIERE DEPURACIÓN FINAL

Restricciones respetadas:
  ✅ No se modificaron datos
  ✅ No se ejecutaron migraciones
  ✅ No se crearon seeders
  ✅ No se corrigieron inconsistencias
  ✅ No se eliminaron registros
  ✅ No se modificó código
  ✅ Auditoría exclusivamente observacional

Cobertura de preguntas:
  P-001 ✅  P-002 ✅  P-003 ✅  P-004 ✅  P-005 ✅
  P-006 ✅  P-007 ✅  P-008 ✅  P-009 ✅  P-010 ✅
```

---

*Generado por Claude Code — BhagamAppsModular Auditoría Técnica — 2026-06-09*
