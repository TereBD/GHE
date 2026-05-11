<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';

final class Grupo
{
    public static function all(): array
    {
        $stmt = Database::connection()->query(
            'SELECT g.*, n.nombre AS nivel_nombre, e.nombre AS etapa,
                    CONCAT(d.nombre, " ", d.apellido) AS tutor_nombre
             FROM grupos g
             JOIN niveles n ON n.id = g.nivel_id
             JOIN etapas e ON e.id = n.etapa_id
             LEFT JOIN docentes d ON d.id = g.tutor_id
             ORDER BY n.orden, g.letra'
        );
        return $stmt->fetchAll();
    }

    public static function reales(): array
    {
        $stmt = Database::connection()->query(
            "SELECT g.*, n.nombre AS nivel_nombre, e.nombre AS etapa,
                    CONCAT(d.nombre, ' ', d.apellido) AS tutor_nombre
             FROM grupos g
             JOIN niveles n ON n.id = g.nivel_id
             JOIN etapas e ON e.id = n.etapa_id
             LEFT JOIN docentes d ON d.id = g.tutor_id
             WHERE g.nombre NOT IN ('Proyecto', 'PAT')
             ORDER BY n.orden, g.letra"
        );
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT g.*, CONCAT(d.nombre, " ", d.apellido) AS tutor_nombre
             FROM grupos g
             LEFT JOIN docentes d ON d.id = g.tutor_id
             WHERE g.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $nombre, int $nivelId, string $letra, ?int $tutorId): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO grupos (nombre, nivel_id, letra, tutor_id) VALUES (:nombre, :nivel_id, :letra, :tutor_id)'
        );
        $stmt->execute(['nombre' => $nombre, 'nivel_id' => $nivelId, 'letra' => $letra, 'tutor_id' => $tutorId]);
    }

    public static function update(int $id, string $nombre, int $nivelId, string $letra, ?int $tutorId): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE grupos SET nombre = :nombre, nivel_id = :nivel_id, letra = :letra, tutor_id = :tutor_id WHERE id = :id'
        );
        $stmt->execute(['id' => $id, 'nombre' => $nombre, 'nivel_id' => $nivelId, 'letra' => $letra, 'tutor_id' => $tutorId]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM grupos WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
