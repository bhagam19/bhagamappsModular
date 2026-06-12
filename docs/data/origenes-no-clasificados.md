# Orígenes No Clasificados Automáticamente — IMPL-INV-012

**Fecha de auditoría:** 2026-06-12
**Total de valores distintos en `bienes.origen` antes de migración:** 27
**Valores clasificados automáticamente sin ambigüedad:** 18
**Valores que requieren verificación humana:** 9

---

## Valores con clasificación automática ambigua

Estos valores fueron clasificados por IMPL-INV-012 pero requieren revisión del
administrador institucional para confirmar o corregir la categoría asignada.

Para corregir: ir a **Inventario → Bienes → columna Origen → editar bien → seleccionar origen correcto**.

| Valor original en `bienes.origen` | Bienes | Clasificado como | Motivo de ambigüedad |
|---|---|---|---|
| `Propiedad De Don Miguel` | 8 | Comodato | Podría ser donación, legado o comodato. "Propiedad De" sugiere uso temporal. |
| `Donación Prom 2018` | 7 | Donación | Clasificado como Donación. El año "2018" y "Prom" (promoción) son datos que se pierden en la normalización. |
| `Donación 2023` | 4 | Donación | Clasificado como Donación. El año "2023" se pierde en la normalización. |
| `Colanta` | 3 | Donación | Colanta es una empresa lechera. Podría ser donación o comodato. Verificar si es préstamo o donación definitiva. |
| `Donacion Governacion` | 2 | Donación | Posible errata de "Gobernación". Verificar si es la Gobernación de Antioquia. |
| `Compra 2023` | 2 | Compra | Clasificado como Compra. El año "2023" se pierde en la normalización. |
| `Donacion Acueducto La Beta` | 1 | Donación | Donación de entidad. "Acueducto La Beta" sugiere empresa de servicios públicos local. |
| `Propiedad De Fritolay` | 1 | Comodato | Bien de Fritolay. Verificar si es comodato activo o si la empresa ya lo cedió definitivamente. |
| `Propiedad De Postobon` | 1 | Comodato | Bien de Postobón. Verificar si es comodato activo o si fue donado. |
| `Parque Explora` | 1 | Donación | Parque Explora es una entidad cultural/educativa de Medellín. Verificar si fue donación directa. |

**Total bienes con clasificación ambigua:** 30

---

## Clasificaciones automáticas sin ambigüedad (referencia)

| Valor original | Origen asignado | Bienes |
|---|---|---|
| `-` | Sin origen | 883 |
| `Institucional` | Institucional | 208 |
| `Donación` | Donación | 164 |
| `Seduca` | SEDUCA | 83 |
| `Municipal` | Municipio | 18 |
| `Donación Colanta` | Donación | 11 |
| `Comodato Madena` | Comodato | 5 |
| `Comodato Fritolay` | Comodato | 4 |
| `Comodato Cocacola` | Comodato | 2 |
| `Comodato` | Comodato | 2 |
| `Compra` | Compra | 2 |
| `Donación Bonanza` | Donación | 2 |
| `Men` | MEN | 2 |
| `Donación Coopecrédito` | Donación | 1 |
| `Mineducación` | MEN | 1 |
| `Comodato Cremhelado` | Comodato | 1 |
| `Comodato Postobón` | Comodato | 1 |
| `Donacion Governacion` → revisión | Donación | 2 |

---

## Totales finales por catálogo

| Origen | Bienes asignados | % del inventario |
|---|---|---|
| Sin origen | 883 | 62.2% |
| Institucional | 208 | 14.6% |
| Donación | 196 | 13.8% |
| SEDUCA | 83 | 5.8% |
| Comodato | 25 | 1.8% |
| Municipio | 18 | 1.3% |
| Compra | 4 | 0.3% |
| MEN | 3 | 0.2% |
| Proyecto | 0 | 0% |
| Transferencia | 0 | 0% |
| Otro | 0 | 0% |
| **Total** | **1,420** | **100%** |

---

## Acción recomendada

1. Revisar los 30 bienes con clasificación ambigua usando el panel de Bienes.
2. Los 883 bienes clasificados como "Sin origen" representan el 62.2% del inventario.
   Buscar documentación histórica para reclasificarlos correctamente.
3. Los catálogos "Proyecto" y "Transferencia" están vacíos — disponibles para uso futuro.
