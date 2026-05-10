<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';

final class NivelModel
{
    public static function all(): array
    {
        return Database::connection()->query(
            'SELECT n.*, e.nombre AS etapa
             FROM niveles n
             JOIN etapas e ON e.id = n.etapa_id
             ORDER BY n.orden'
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT n.*, e.nombre AS etapa
             FROM niveles n
             JOIN etapas e ON e.id = n.etapa_id
             WHERE n.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $nombre, int $etapaId, int $orden): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO niveles (nombre, etapa_id, orden) VALUES (:nombre, :etapa_id, :orden)'
        );
        $stmt->execute(['nombre' => $nombre, 'etapa_id' => $etapaId, 'orden' => $orden]);
    }

    public static function update(int $id, string $nombre, int $etapaId, int $orden): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE niveles SET nombre = :nombre, etapa_id = :etapa_id, orden = :orden WHERE id = :id'
        );
        $stmt->execute(['id' => $id, 'nombre' => $nombre, 'etapa_id' => $etapaId, 'orden' => $orden]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM niveles WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
