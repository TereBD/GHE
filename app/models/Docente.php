<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';

final class Docente
{
    public static function all(): array
    {
        return Database::connection()->query('SELECT * FROM docentes ORDER BY apellido, nombre')->fetchAll();
    }

    public static function tutores(): array
    {
        $stmt = Database::connection()->query("SELECT * FROM docentes WHERE tipo = 'tutor' ORDER BY apellido, nombre");
        return $stmt->fetchAll();
    }

    public static function especialistas(): array
    {
        $stmt = Database::connection()->query("SELECT * FROM docentes WHERE tipo = 'especialista' ORDER BY apellido, nombre");
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
            'INSERT INTO docentes (nombre, apellido, tipo, horas_maximas, horas_pat, horas_proyecto, dias_excluidos)
             VALUES (:nombre, :apellido, :tipo, :horas_maximas, :horas_pat, :horas_proyecto, :dias_excluidos)'
        );
        $stmt->execute([
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'tipo' => $data['tipo'] ?? 'tutor',
            'horas_maximas' => $data['horas_maximas'],
            'horas_pat' => $data['horas_pat'],
            'horas_proyecto' => $data['horas_proyecto'],
            'dias_excluidos' => !empty($data['dias_excluidos']) ? $data['dias_excluidos'] : null,
        ]);
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE docentes
             SET nombre = :nombre, apellido = :apellido, tipo = :tipo,
                 horas_maximas = :horas_maximas, horas_pat = :horas_pat,
                 horas_proyecto = :horas_proyecto, dias_excluidos = :dias_excluidos
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'tipo' => $data['tipo'] ?? 'tutor',
            'horas_maximas' => $data['horas_maximas'],
            'horas_pat' => $data['horas_pat'],
            'horas_proyecto' => $data['horas_proyecto'],
            'dias_excluidos' => !empty($data['dias_excluidos']) ? $data['dias_excluidos'] : null,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM docentes WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
