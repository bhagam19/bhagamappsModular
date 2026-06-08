# ADR-002 — Estrategia Livewire-First para Interactividad de UI

**Estado:** Aceptado
**Fecha:** [fecha de inicio del proyecto]
**Contexto:** BhagamApps Modular — capa de presentación e interactividad

---

## Contexto

BhagamApps necesita interfaces interactivas (edición inline, filtros dinámicos,
modales, actualizaciones parciales de página) sin introducir la complejidad de una
SPA (React/Vue) ni el overhead de mantener una API REST separada para el frontend.

El sistema corre en un subdirectorio (`/Modular`) bajo un dominio con SSL y panel
de control HestiaCP, lo que impone restricciones en la configuración del endpoint
de Livewire.

---

## Decisión

Se adopta **Livewire 3** como estrategia principal para toda la interactividad de UI.

Las interfaces de los módulos se construyen como componentes Livewire bajo
`Modules/<Nombre>/Livewire/`. No se usa JavaScript personalizado salvo cuando
Livewire no pueda satisfacer el requisito.

**Configuración especial para subdirectorio:**

```php
// app/Providers/AppServiceProvider.php
Livewire::setUpdateRoute(function ($handle) {
    return Route::post('/Modular/livewire/update', $handle);
});
```

Esta línea es **crítica**. Sin ella, los requests de Livewire apuntan a `/livewire/update`
(sin el prefijo `/Modular`) y todas las interacciones fallan en producción.

---

## Stack de UI por capa

| Capa     | Tecnología                           | Alcance                |
|----------|--------------------------------------|------------------------|
| Layout   | AdminLTE 3 + Bootstrap 4             | Módulos                |
| CSS core | Tailwind CSS 3                       | Vistas de auth y core  |
| JS       | Alpine.js (incluido con Livewire 3)  | Interactividad ligera  |
| Ajax     | Livewire 3 (wire:model, wire:click)  | Toda la UI de módulos  |

**Coexistencia AdminLTE / Tailwind:** los módulos usan `@extends('inventario::layouts.master')`
con AdminLTE. Las vistas de autenticación y el dashboard usan layouts con Tailwind.
No se mezclan en la misma vista.

---

## Consecuencias positivas

- Un solo lenguaje (PHP/Blade) para backend y frontend.
- Los componentes Livewire son testeables como clases PHP.
- El estado del componente es gestionado en servidor → no hay desincronización.
- Alpine.js disponible sin configuración adicional para microinteracciones.

## Consecuencias negativas y mitigaciones

| Consecuencia | Mitigación |
|---|---|
| Cada interacción hace un request HTTP al servidor | Acceptable para el volumen de usuarios (institución educativa, ~116 usuarios) |
| Livewire en subdirectorio requiere `setUpdateRoute()` | Documentado y configurado. Si se mueve el subdirectorio, solo cambia esa línea. |
| Coexistencia Bootstrap 4 / Tailwind puede generar conflictos CSS | Separación estricta por layout. No mezclar en la misma vista. |

---

## Alternativas consideradas

- **Inertia.js + Vue:** descartado por curva de aprendizaje y separación de contexto
  PHP/JS para el equipo actual.
- **JavaScript vanilla / jQuery:** descartado por mantenibilidad a largo plazo.
- **API REST + SPA:** descartado por duplicar la capa de autorización y complejidad
  innecesaria para el tamaño del proyecto.
