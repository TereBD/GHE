<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';

final class NivelAsignatura
{
    public static function allDetailed(): array
    {
        $sql = 'SELECT na.id, na.nivel_id, na.sesiones_semana, n.nombre AS nivel, e.nombre AS etapa, a.nombre AS asignatura
                FROM nivel_asignatura na
                JOIN niveles n ON n.id = na.nivel_id
                JOIN etapas e ON e.id = n.etapa_id
                JOIN asignaturas a ON a.id = na.asignatura_id
                ORDER BY n.orden, a.nombre';
        return Database::connection()->query($sql)->fetchAll();
    }

    public static function allByNivel(): array
    {
        $items = self::allDetailed();
        $agrupado = [];
        foreach ($items as $item) {
            $agrupado[$item['nivel']][] = $item;
        }
        return $agrupado;
    }

    public static function matrixData(): array
    {
        $niveles = Database::connection()->query(
            'SELECT n.*, e.nombre AS etapa FROM niveles n JOIN etapas e ON e.id = n.etapa_id ORDER BY n.orden'
        )->fetchAll();
        $asignaturas = Database::connection()->query('SELECT * FROM asignaturas ORDER BY nombre')->fetchAll();
        $rows = Database::connection()->query(
            'SELECT na.nivel_id, na.asignatura_id, na.sesiones_semana FROM nivel_asignatura na'
        )->fetchAll();

        $map = [];
        foreach ($rows as $r) {
            $map[$r['nivel_id'] . '-' . $r['asignatura_id']] = (int) $r['sesiones_semana'];
        }

        return ['niveles' => $niveles, 'asignaturas' => $asignaturas, 'map' => $map];
    }

    public static function create(int $nivelId, int $asignaturaId, int $sesiones): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO nivel_asignatura (nivel_id, asignatura_id, sesiones_semana)
             VALUES (:nivel_id, :asignatura_id, :sesiones_semana)'
        );
        $stmt->execute([
            'nivel_id' => $nivelId,
            'asignatura_id' => $asignaturaId,
            'sesiones_semana' => $sesiones,
        ]);
    }

    public static function upsert(int $nivelId, int $asignaturaId, int $sesiones): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO nivel_asignatura (nivel_id, asignatura_id, sesiones_semana) VALUES (:nivel_id, :asignatura_id, :sesiones_semana)
             ON DUPLICATE KEY UPDATE sesiones_semana = :sesiones_semana2'
        );
        $stmt->execute([
            'nivel_id' => $nivelId,
            'asignatura_id' => $asignaturaId,
            'sesiones_semana' => $sesiones,
            'sesiones_semana2' => $sesiones,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM nivel_asignatura WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
