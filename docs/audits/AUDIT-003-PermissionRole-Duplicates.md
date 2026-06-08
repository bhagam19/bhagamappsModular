# AUDIT-003 — Duplicados en permission_role

**Fecha de auditoría:** 2026-06-08
**Referencia:** IMPL-003 — PermissionRole Cleanup
**Estado:** COMPLETADO — Análisis previo a la ejecución de IMPL-003.

---

## Resumen Ejecutivo

| Métrica                       | Valor |
|-------------------------------|-------|
| Total registros (antes)       | 156   |
| Combinaciones únicas          | 80    |
| Registros duplicados          | 76    |
| Duplicados máximos por par    | 2 (exacto) |
| Pares con más de 2 ocurrencias | 0 (sin anomalías) |
| Pares con 1 sola ocurrencia    | 4 (Coordinador + permisos de usuario) |

**Causa raíz:** El seeder `Permission_RoleSeeder` fue ejecutado dos veces en producción.
La primera ejecución creó los registros con `created_at = NULL`; la segunda (2026-06-07 12:13:20)
generó los duplicados. No existe constraint UNIQUE en la tabla, lo que permitió las inserciones
repetidas sin error.

**Anomalías detectadas:** Ninguna. Los 4 pares de Coordinador con 1 sola ocurrencia
(permisos `ver-usuarios`, `crear-usuarios`, `editar-usuarios`, `eliminar-usuarios`) son legítimos:
existían únicamente en la segunda ejecución del seeder, correspondiendo a permisos nuevos
agregados en `Users-v1.1.0`.

---

## Detalle por combinación

### Administrador (role_id = 1) — 26 duplicados

| permission_id | Permiso                                   | Total | id conservar | id eliminar |
|---|---|---|---|---|
| 1  | ver-usuarios                              | 2 | 27  | 103 |
| 2  | crear-usuarios                            | 2 | 9   | 85  |
| 3  | editar-usuarios                           | 2 | 14  | 90  |
| 4  | eliminar-usuarios                         | 2 | 19  | 95  |
| 5  | ver-roles                                 | 2 | 26  | 102 |
| 6  | crear-roles                               | 2 | 8   | 84  |
| 7  | editar-roles                              | 2 | 13  | 89  |
| 8  | eliminar-roles                            | 2 | 18  | 94  |
| 9  | asignar-permisos-a-roles                  | 2 | 4   | 80  |
| 10 | ver-permisos                              | 2 | 25  | 101 |
| 11 | crear-permisos                            | 2 | 7   | 83  |
| 12 | editar-permisos                           | 2 | 12  | 88  |
| 13 | eliminar-permisos                         | 2 | 17  | 93  |
| 14 | ver-bienes                                | 2 | 22  | 98  |
| 15 | crear-bienes                              | 2 | 6   | 82  |
| 16 | editar-bienes                             | 2 | 11  | 87  |
| 17 | eliminar-bienes                           | 2 | 16  | 92  |
| 18 | aprobar-bienes                            | 2 | 2   | 78  |
| 19 | ver-historial-bienes                      | 2 | 23  | 99  |
| 20 | asignar-responsables-a-bienes             | 2 | 5   | 81  |
| 21 | ver-imagenes-de-bienes                    | 2 | 24  | 100 |
| 22 | gestionar-historial-modificaciones-bienes | 2 | 21  | 97  |
| 23 | aprobar-pendientes-bienes                 | 2 | 3   | 79  |
| 24 | editar-aprobaciones-pendientes-bienes     | 2 | 10  | 86  |
| 25 | eliminar-aprobaciones-pendientes-bienes   | 2 | 15  | 91  |
| 26 | ver-actas-de-entrega                      | 2 | 20  | 96  |

### Rector (role_id = 2) — 26 duplicados

| permission_id | Permiso                                   | Total | id conservar | id eliminar |
|---|---|---|---|---|
| 1  | ver-usuarios                              | 2 | 53  | 129 |
| 2  | crear-usuarios                            | 2 | 35  | 111 |
| 3  | editar-usuarios                           | 2 | 40  | 116 |
| 4  | eliminar-usuarios                         | 2 | 45  | 121 |
| 5  | ver-roles                                 | 2 | 52  | 128 |
| 6  | crear-roles                               | 2 | 34  | 110 |
| 7  | editar-roles                              | 2 | 39  | 115 |
| 8  | eliminar-roles                            | 2 | 44  | 120 |
| 9  | asignar-permisos-a-roles                  | 2 | 30  | 106 |
| 10 | ver-permisos                              | 2 | 51  | 127 |
| 11 | crear-permisos                            | 2 | 33  | 109 |
| 12 | editar-permisos                           | 2 | 38  | 114 |
| 13 | eliminar-permisos                         | 2 | 43  | 119 |
| 14 | ver-bienes                                | 2 | 48  | 124 |
| 15 | crear-bienes                              | 2 | 32  | 108 |
| 16 | editar-bienes                             | 2 | 37  | 113 |
| 17 | eliminar-bienes                           | 2 | 42  | 118 |
| 18 | aprobar-bienes                            | 2 | 28  | 104 |
| 19 | ver-historial-bienes                      | 2 | 49  | 125 |
| 20 | asignar-responsables-a-bienes             | 2 | 31  | 107 |
| 21 | ver-imagenes-de-bienes                    | 2 | 50  | 126 |
| 22 | gestionar-historial-modificaciones-bienes | 2 | 47  | 123 |
| 23 | aprobar-pendientes-bienes                 | 2 | 29  | 105 |
| 24 | editar-aprobaciones-pendientes-bienes     | 2 | 36  | 112 |
| 25 | eliminar-aprobaciones-pendientes-bienes   | 2 | 41  | 117 |
| 26 | ver-actas-de-entrega                      | 2 | 46  | 122 |

### Coordinador (role_id = 3) — 8 duplicados + 4 singulares legítimos

| permission_id | Permiso               | Total | Estado   | id conservar | id eliminar |
|---|---|---|---|---|---|
| 1  | ver-usuarios          | 1 | ÚNICO (legítimo) | 130 | — |
| 2  | crear-usuarios        | 1 | ÚNICO (legítimo) | 131 | — |
| 3  | editar-usuarios       | 1 | ÚNICO (legítimo) | 132 | — |
| 4  | eliminar-usuarios     | 1 | ÚNICO (legítimo) | 133 | — |
| 14 | ver-bienes            | 2 | duplicado        | 54  | 134 |
| 15 | crear-bienes          | 2 | duplicado        | 55  | 135 |
| 16 | editar-bienes         | 2 | duplicado        | 56  | 136 |
| 17 | eliminar-bienes       | 2 | duplicado        | 57  | 137 |
| 18 | aprobar-bienes        | 2 | duplicado        | 58  | 138 |
| 19 | ver-historial-bienes  | 2 | duplicado        | 59  | 139 |
| 20 | asignar-responsables  | 2 | duplicado        | 60  | 140 |
| 21 | ver-imagenes          | 2 | duplicado        | 61  | 141 |

### Auxiliar (role_id = 4) — 8 duplicados

ids eliminados: 150, 151, 152, 153, 154, 155, 156, 157

### Docente (role_id = 5) — 8 duplicados

ids eliminados: 142, 143, 144, 145, 146, 147, 148, 149

---

## Resumen por rol

| Rol           | Registros antes | Permisos únicos | Duplicados eliminados | Registros después |
|---------------|-----------------|-----------------|----------------------|-------------------|
| Administrador | 52              | 26              | 26                   | 26                |
| Rector        | 52              | 26              | 26                   | 26                |
| Coordinador   | 20              | 12              | 8                    | 12                |
| Auxiliar      | 16              | 8               | 8                    | 8                 |
| Docente       | 16              | 8               | 8                    | 8                 |
| **Total**     | **156**         | **80**          | **76**               | **80**            |

---

*Auditoría de solo lectura realizada antes de ejecutar IMPL-003.*
*Ver `docs/impl/IMPL-003-PermissionRole-Cleanup.md` para el detalle de la implementación.*
