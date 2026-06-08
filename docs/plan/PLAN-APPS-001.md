# PLAN-APPS-001 — Plan de Implementación del Módulo Central de Aplicaciones

**Fecha:** 2026-06-08
**Relacionado con:** ADR-APPS-001, AUDIT-APPS-001
**Implementación:** IMPL-APPS-001

---

## Objetivos

1. Ampliar la tabla `apps` con campos de metadatos
2. Crear tabla `app_role` para autorización por rol
3. Implementar `App::visiblesPara($user)`
4. Corregir bug en `AppController::index()`
5. Actualizar `AppsServiceProvider` (migraciones, Livewire, comando)
6. Crear componente Livewire `AppsIndex` (panel admin)
7. Crear comando `apps:sync`
8. Actualizar `HomeController` para usar `App::visiblesPara()`
9. Actualizar seeder con nuevos campos
10. Documentar en IMPL-APPS-001 y changelog

---

## Pasos de implementación

### Bloque 1 — Documentación previa (no código)
- [x] AUDIT-APPS-001
- [x] ADR-APPS-001
- [x] PLAN-APPS-001

### Bloque 2 — Migraciones (aditivas, no destructivas)
- [ ] `2026_06_08_100000_add_fields_to_apps_table.php` — agrega slug, descripcion, icono, color, orden
- [ ] `2026_06_08_100001_create_app_role_table.php` — crea pivot app_role

### Bloque 3 — Modelo App
- [ ] Agregar fillables nuevos (slug, descripcion, icono, color, orden)
- [ ] Agregar relación `roles()` → belongsToMany Role via app_role
- [ ] Agregar método estático `visiblesPara($user)`
- [ ] Preservar relación `user()` existente

### Bloque 4 — Controlador (corrección bug)
- [ ] Reemplazar `App::where('user_id', ...)` por query correcta en `AppController::index()`

### Bloque 5 — ServiceProvider
- [ ] Actualizar AppsServiceProvider con patrón Inventario:
  - Auto-registro Livewire
  - `loadMigrationsFrom`
  - Registro comando `apps:sync`

### Bloque 6 — Livewire (panel admin)
- [ ] Crear `Modules/Apps/Livewire/AppsIndex.php`
- [ ] Crear `Modules/Apps/resources/views/livewire/apps-index.blade.php`
- [ ] Crear vista `Modules/Apps/resources/views/admin/index.blade.php` (wrapper)

### Bloque 7 — Comando Artisan
- [ ] Crear `Modules/Apps/Console/Commands/SyncApps.php`
- [ ] Registrar en `AppsServiceProvider`

### Bloque 8 — Rutas
- [ ] Agregar ruta `/apps/admin` con nombre `apps.admin.index`
- [ ] Middleware `auth` + verificación de rol admin

### Bloque 9 — Core (HomeController)
- [ ] Actualizar `HomeController::index()` para usar `App::visiblesPara(auth()->user())`

### Bloque 10 — Seeder
- [ ] Actualizar `AppSeeder` con slug, descripcion, icono, color, orden

### Bloque 11 — Documentación post-implementación
- [ ] IMPL-APPS-001
- [ ] Actualizar `docs/changelog/apps.md` → v1.1.0

---

## Restricciones

- No modificar la tabla `app_user` ni sus datos
- No eliminar el campo `imagen` de la tabla `apps`
- No modificar `Modules/User` ni `Modules/Inventario`
- No ejecutar migraciones destructivas
- El comando `apps:sync` no asigna roles automáticamente

---

## Criterios de éxito

- [ ] `php artisan migrate` corre sin errores
- [ ] `App::visiblesPara(auth()->user())` retorna apps del rol del usuario
- [ ] El dashboard muestra solo apps autorizadas
- [ ] `php artisan apps:sync` registra módulos nWidart en la tabla apps
- [ ] El panel admin en `/apps/admin` lista apps y permite toggle de habilitada
- [ ] No hay errores en los módulos existentes (Inventario, User)
