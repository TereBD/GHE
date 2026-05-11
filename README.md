# GestHorarios Escolares (MVP)

Aplicacion base en PHP 8 + MySQL para gestion de docentes y generacion automatica de horarios.

## Requisitos

- XAMPP (Apache + MySQL)
- PHP 8
- MySQL 8

## Instalacion

1. Copiar la carpeta `GHE` dentro de `htdocs`.
2. Crear base de datos y tablas ejecutando:
   - `database/schema.sql`
   - `database/seed.sql`
3. Revisar credenciales en `config.php`.
4. Abrir en navegador:
   - `http://localhost/GHE/public/index.php`

## Funcionalidades incluidas

- CRUD de docentes.
- CRUD de asignaturas.
- CRUD de grupos.
- Gestion de asignaciones docente-asignatura-grupo.
- Gestion de distribucion horaria (sesiones por grupo y asignatura).
- Generacion automatica basica de horarios segun:
  - Distribucion de sesiones por grupo/asignatura.
  - Compatibilidad docente-asignatura-grupo.
  - No solape de docente ni grupo en el mismo dia/sesion.
  - Respeto de horas maximas del docente.
  - Busqueda con backtracking y heuristicas para resolver casos complejos.
  - Reparto mas equilibrado por docente y por dia.
