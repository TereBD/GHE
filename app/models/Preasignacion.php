<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';

final class Preasignacion
{
    public static function all(): array
    {
        $pdo = Database::connection();
        return $pdo->query(
            'SELECT p.id, p.docente_id, p.grupo_id, p.asignatura_id, p.dia_semana, p.sesion,
                    CONCAT(d.nombre, " ", d.apellido) AS docente,
                    a.nombre AS asignatura,
                    g.nombre AS grupo
             FROM preasignaciones p
             JOIN docentes d ON d.id = p.docente_id
             JOIN asignaturas a ON a.id = p.asignatura_id
             JOIN grupos g ON g.id = p.grupo_id
             ORDER BY p.dia_semana, p.sesion, d.apellido'
        )->fetchAll();
    }

    public static function allAsMap(): array
    {
        $pdo = Database::connection();
        $rows = $pdo->query(
            'SELECT docente_id, grupo_id, asignatura_id, dia_semana, sesion FROM preasignaciones'
        )->fetchAll();

        $docenteSlots = [];
        $grupoSlots = [];
        $details = [];

        foreach ($rows as $r) {
            $slot = $r['dia_semana'] . '-' . $r['sesion'];
            $docenteSlots[(int) $r['docente_id']][$slot] = true;
            $grupoSlots[(int) $r['grupo_id']][$slot] = true;
            $details[] = $r;
        }

        return [
            'docenteSlots' => $docenteSlots,
            'grupoSlots' => $grupoSlots,
            'details' => $details,
        ];
    }

    public static function asignar(int $docenteId, int $grupoId, int $asignaturaId, string $dia, int $sesion): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO preasignaciones (docente_id, grupo_id, asignatura_id, dia_semana, sesion)
             VALUES (:docente_id, :grupo_id, :asignatura_id, :dia_semana, :sesion)'
        );
        $stmt->execute([
            'docente_id' => $docenteId,
            'grupo_id' => $grupoId,
            'asignatura_id' => $asignaturaId,
            'dia_semana' => $dia,
            'sesion' => $sesion,
        ]);
    }

    public static function eliminar(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM preasignaciones WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function asignaturasDisponibles(int $docenteId, int $grupoId): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT a.id, a.nombre
             FROM docente_asig_grupo dag
             JOIN asignaturas a ON a.id = dag.asignatura_id
             WHERE dag.docente_id = :docente_id AND dag.grupo_id = :grupo_id AND dag.es_tutoria = 0
             ORDER BY a.nombre'
        );
        $stmt->execute(['docente_id' => $docenteId, 'grupo_id' => $grupoId]);
        return $stmt->fetchAll();
    }
}
