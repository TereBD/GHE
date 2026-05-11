<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';

final class Docente
{
    public static function all(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM docentes ORDER BY apellido, nombre');
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM docentes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO docentes (nombre, apellido, horas_maximas, horas_pat_proyecto)
             VALUES (:nombre, :apellido, :horas_maximas, :horas_pat_proyecto)'
        );
        $stmt->execute([
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'horas_maximas' => $data['horas_maximas'],
            'horas_pat_proyecto' => $data['horas_pat_proyecto'],
        ]);
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE docentes
             SET nombre = :nombre, apellido = :apellido, horas_maximas = :horas_maximas, horas_pat_proyecto = :horas_pat_proyecto
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'horas_maximas' => $data['horas_maximas'],
            'horas_pat_proyecto' => $data['horas_pat_proyecto'],
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM docentes WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
