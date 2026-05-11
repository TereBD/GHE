<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';

final class DocenteAsignacion
{
    public static function allDetailed(): array
    {
        $sql = 'SELECT dag.id, CONCAT(d.nombre, " ", d.apellido) AS docente, a.nombre AS asignatura, g.nombre AS grupo
                FROM docente_asig_grupo dag
                JOIN docentes d ON d.id = dag.docente_id
                JOIN asignaturas a ON a.id = dag.asignatura_id
                JOIN grupos g ON g.id = dag.grupo_id
                ORDER BY docente, g.nombre, a.nombre';
        return Database::connection()->query($sql)->fetchAll();
    }

    public static function create(int $docenteId, int $asignaturaId, int $grupoId): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO docente_asig_grupo (docente_id, asignatura_id, grupo_id)
             VALUES (:docente_id, :asignatura_id, :grupo_id)'
        );
        $stmt->execute([
            'docente_id' => $docenteId,
            'asignatura_id' => $asignaturaId,
            'grupo_id' => $grupoId,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM docente_asig_grupo WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
