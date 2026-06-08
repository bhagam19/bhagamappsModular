# AUDIT-004 — API Authentication Assessment

**Fecha:** 2026-06-08
**Estado:** APROBADO
**Aprobado por:** Dirección General
**Módulo:** Transversal — API Layer

---

## 1. Objetivo

Verificar si las rutas API de BhagamAppsModular cuentan con autenticación activa,
en respuesta al riesgo R-04 identificado en BASELINE-001:

> *"Rutas API sin autenticación → exposición de datos de inventario"*
> Probabilidad: Media | Impacto: Alto

---

## 2. Alcance

| Ruta | Métodos | Módulo |
|---|---|---|
| `api/v1/inventarios` | GET, POST, GET/{id}, PUT/{id}, DELETE/{id} | Inventario |
| `api/v1/crudgenerators` | GET, POST, GET/{id}, PUT/{id}, DELETE/{id} | CrudGenerator |
| `api/user` | GET | Core (Laravel) |

Total rutas auditadas: **11**

---

## 3. Metodología

Inspección directa mediante:

```bash
php artisan route:list --path=api --json
```

Confirmación de middleware por ruta. Verificación del estado de la tabla
`personal_access_tokens`. Revisión de controladores asociados.

---

## 4. Hallazgos

### H-01 — Autenticación activa en todas las rutas API ✅ RESUELTO

**Severidad:** N/A (hallazgo positivo)

Todas las rutas bajo `api/v1/` tienen el middleware `auth:sanctum` activo y confirmado.

```
api/v1/inventarios          middleware: ['api', 'Authenticate:sanctum']
api/v1/inventarios/{id}     middleware: ['api', 'Authenticate:sanctum']
api/v1/crudgenerators       middleware: ['api', 'Authenticate:sanctum']
api/v1/crudgenerators/{id}  middleware: ['api', 'Authenticate:sanctum']
api/user                    middleware: ['api', 'Authenticate:sanctum']
```

El riesgo R-04 de BASELINE-001 queda **cerrado**.

---

### H-02 — API no está en uso activo ℹ️ INFORMATIVO

**Severidad:** Informativa

La tabla `personal_access_tokens` contiene **0 registros**. Ningún token Sanctum
ha sido emitido. La API no está siendo consumida por ningún cliente.

Esto reduce el riesgo superficial a cero en el estado actual.

---

### H-03 — InventarioController devuelve 501 en todos los métodos ℹ️ INFORMATIVO

**Severidad:** Informativa

```php
public function index(): JsonResponse
{
    return response()->json(['message' => 'Not implemented'], 501);
}
```

Todos los métodos del `InventarioController` retornan `501 Not Implemented`.
La API de inventario existe como scaffolding pero no tiene implementación funcional.

No representa un riesgo de exposición de datos — no hay datos que exponer.

---

### H-04 — CrudGeneratorController registrado en rutas API retorna vistas Blade ⚠️ MENOR

**Severidad:** Menor

```php
// Modules/CrudGenerator/Http/Controllers/CrudGeneratorController.php
public function index()
{
    return view('crudgenerator::index');  // retorna HTML, no JSON
}
```

El controlador `CrudGeneratorController` es en realidad un controlador web
(retorna vistas Blade) registrado en rutas API (`api/v1/crudgenerators`).

Esta es una incongruencia arquitectónica:
- Las rutas API deberían retornar JSON.
- El controlador retorna HTML para un consumidor de API que espera JSON.

**Mitigación actual:** la ruta requiere `auth:sanctum`. El error sería funcional
(respuesta HTML inesperada), no un riesgo de seguridad.

---

### H-05 — CORS configurado como wildcard ⚠️ MENOR

**Severidad:** Menor (contexto: aplicación institucional interna)

```php
// config/cors.php
'allowed_origins' => ['*'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'supports_credentials' => false,
```

La configuración CORS permite peticiones desde cualquier origen. Para una
aplicación de gestión institucional sin consumo externo previsto, esta
configuración es más permisiva de lo necesario.

**Mitigación actual:** `supports_credentials: false` + `auth:sanctum` requiere
token en header. Sin token válido no es posible acceder a datos.

---

### H-06 — Sin rate limiting en rutas API ⚠️ MENOR

**Severidad:** Menor

El grupo de middleware `api` no incluye `throttle:*`. No hay limitación de
peticiones por IP o usuario en las rutas API.

En el estado actual (0 tokens, API no usada) el riesgo es mínimo. Será relevante
cuando la API sea puesta en uso activo.

---

### H-07 — Rutas API de módulos User y Apps no están registradas ℹ️ INFORMATIVO

**Severidad:** Informativa

Los archivos `Modules/User/Routes/api.php` y `Modules/Apps/routes/api.php` existen
en el repositorio y definen rutas con `auth:sanctum`, pero estas rutas **no aparecen**
en `php artisan route:list`. No están siendo cargadas por el service provider.

Esto significa que esas rutas no están activas — no representan riesgo de exposición.

---

## 5. Tabla resumen de hallazgos

| ID | Hallazgo | Severidad | Estado |
|---|---|---|---|
| H-01 | auth:sanctum activo en todas las rutas | Positivo | ✅ Resuelto |
| H-02 | API no en uso activo (0 tokens) | Informativo | ℹ️ Monitorear |
| H-03 | InventarioController retorna 501 | Informativo | ℹ️ Diseño previsto |
| H-04 | CrudGeneratorController retorna vistas en ruta API | Menor | ⚠️ Pendiente |
| H-05 | CORS wildcard | Menor | ⚠️ Pendiente |
| H-06 | Sin rate limiting en API | Menor | ⚠️ Pendiente |
| H-07 | Rutas User/Apps no cargadas | Informativo | ℹ️ Verificar intención |

---

## 6. Impacto en BASELINE-001

| Riesgo original | Estado anterior | Estado actual |
|---|---|---|
| R-04: API sin autenticación → exposición | ⚠️ ALTA | ✅ **CERRADO** |

---

## 7. Recomendaciones

### R-01 — Separar CrudGeneratorController en controlador API dedicado

Crear `CrudGeneratorApiController` con respuestas JSON para `api/v1/crudgenerators`,
manteniendo `CrudGeneratorController` solo para rutas web.

Prioridad: Baja (API no usada actualmente).

---

### R-02 — Restringir CORS al dominio institucional cuando la API sea activada

Al activar la API para uso real, cambiar:

```php
'allowed_origins' => ['https://bhagamapps.com'],
```

Prioridad: Media (hacer al momento de activar el uso real de la API).

---

### R-03 — Agregar throttle a rutas API cuando sean activadas

```php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('v1')->group(...)
```

Prioridad: Media (hacer al momento de activar el uso real de la API).

---

### R-04 — Verificar intención de rutas User y Apps

Determinar si `Modules/User/Routes/api.php` y `Modules/Apps/routes/api.php`
deben activarse o eliminarse del repositorio.

Prioridad: Baja.

---

## 8. Impacto en ROADMAP-001

**Fase 1 — Gobierno y Estabilización:**

El hito `AUDIT-004 — API Authentication` queda completado.

Las recomendaciones H-04, H-05, H-06 son de severidad menor y no bloquean la
**Fase 2 — Inventario Operativo**. Pueden ser abordadas en Fase 4 (Consolidación).

---

## 9. Conclusión

**El riesgo crítico identificado en BASELINE-001 queda cerrado.**

Todas las rutas API de BhagamAppsModular cuentan con autenticación `auth:sanctum`
activa y verificada. La API no está en uso activo (0 tokens emitidos) y los
controladores existentes son placeholders sin datos sensibles expuestos.

Los hallazgos menores (H-04, H-05, H-06) son apropiados para el estado actual del
proyecto y no requieren acción inmediata antes de la Fase 2.
