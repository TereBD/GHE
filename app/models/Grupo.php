<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';

final class Grupo
{
    public static function all(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM grupos ORDER BY nombre');
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM grupos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $nombre): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO grupos (nombre) VALUES (:nombre)');
        $stmt->execute(['nombre' => $nombre]);
    }

    public static function update(int $id, string $nombre): void
    {
        $stmt = Database::connection()->prepare('UPDATE grupos SET nombre = :nombre WHERE id = :id');
        $stmt->execute(['id' => $id, 'nombre' => $nombre]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM grupos WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
