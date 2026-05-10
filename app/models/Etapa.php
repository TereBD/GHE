<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';

final class Etapa
{
    public static function all(): array
    {
        return Database::connection()->query('SELECT * FROM etapas ORDER BY orden')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM etapas WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $nombre, int $orden): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO etapas (nombre, orden) VALUES (:nombre, :orden)');
        $stmt->execute(['nombre' => $nombre, 'orden' => $orden]);
    }

    public static function update(int $id, string $nombre, int $orden): void
    {
        $stmt = Database::connection()->prepare('UPDATE etapas SET nombre = :nombre, orden = :orden WHERE id = :id');
        $stmt->execute(['id' => $id, 'nombre' => $nombre, 'orden' => $orden]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM etapas WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
