<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';

final class DistribucionHoraria
{
    public static function allDetailed(): array
    {
        $sql = 'SELECT dh.id, dh.sesiones_semana, g.nombre AS grupo, a.nombre AS asignatura
                FROM distribucion_horaria dh
                JOIN grupos g ON g.id = dh.grupo_id
                JOIN asignaturas a ON a.id = dh.asignatura_id
                ORDER BY g.nombre, a.nombre';
        return Database::connection()->query($sql)->fetchAll();
    }

    public static function create(int $grupoId, int $asignaturaId, int $sesionesSemana): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO distribucion_horaria (grupo_id, asignatura_id, sesiones_semana)
             VALUES (:grupo_id, :asignatura_id, :sesiones_semana)'
        );
        $stmt->execute([
            'grupo_id' => $grupoId,
            'asignatura_id' => $asignaturaId,
            'sesiones_semana' => $sesionesSemana,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM distribucion_horaria WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
