<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';

final class HorarioGenerator
{
    private const DIAS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
    private const SESIONES_DIA = 6;
    private const SESIONES_EXCLUIDAS = [4];
    private const MAX_NODOS_BUSQUEDA = 200000;

    public static function generar(): array
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $pdo->exec('DELETE FROM horarios');

            $docentes = self::cargarDocentes($pdo);
            $elegibles = self::cargarElegibles($pdo);
            $tareas = self::cargarTareas($pdo);
            self::validarEscenario($tareas, $elegibles);

            $estado = [
                'ocupacionGrupo' => [],
                'ocupacionDocente' => [],
                'cargaDocente' => array_fill_keys(array_keys($docentes), 0),
                'cargaGrupoDia' => [],
                'cargaDocenteDia' => [],
                'asignaciones' => [],
                'nodos' => 0,
            ];

            if (!self::resolver($tareas, $estado, $docentes, $elegibles)) {
                throw new RuntimeException(
                    'No fue posible generar un horario valido con las restricciones actuales. ' .
                    'Revisa asignaciones docente-asignatura-grupo, horas maximas o distribucion horaria.'
                );
            }

            $insert = $pdo->prepare(
                'INSERT INTO horarios (grupo_id, dia_semana, sesion, asignatura_id, docente_id)
                 VALUES (:grupo_id, :dia_semana, :sesion, :asignatura_id, :docente_id)'
            );
            foreach ($estado['asignaciones'] as $asignacion) {
                $insert->execute([
                    'grupo_id' => $asignacion['grupo_id'],
                    'dia_semana' => $asignacion['dia'],
                    'sesion' => $asignacion['sesion'],
                    'asignatura_id' => $asignacion['asignatura_id'],
                    'docente_id' => $asignacion['docente_id'],
                ]);
            }

            $pdo->commit();

            return $pdo->query(
                'SELECT h.dia_semana, h.sesion, g.nombre AS grupo, a.nombre AS asignatura, CONCAT(d.nombre, " ", d.apellido) AS docente
                 FROM horarios h
                 JOIN grupos g ON g.id = h.grupo_id
                 JOIN asignaturas a ON a.id = h.asignatura_id
                 JOIN docentes d ON d.id = h.docente_id
                 ORDER BY g.nombre, FIELD(h.dia_semana, "Lunes", "Martes", "Miércoles", "Jueves", "Viernes"), h.sesion'
            )->fetchAll();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private static function cargarDocentes(PDO $pdo): array
    {
        $rows = $pdo->query('SELECT id, horas_maximas FROM docentes')->fetchAll();
        $docentes = [];
        foreach ($rows as $row) {
            $docentes[(int) $row['id']] = ['horas_maximas' => (int) $row['horas_maximas']];
        }
        return $docentes;
    }

    private static function cargarElegibles(PDO $pdo): array
    {
        $rows = $pdo->query('SELECT docente_id, asignatura_id, grupo_id FROM docente_asig_grupo')->fetchAll();
        $mapa = [];
        foreach ($rows as $row) {
            $clave = (int) $row['grupo_id'] . '-' . (int) $row['asignatura_id'];
            $mapa[$clave][] = (int) $row['docente_id'];
        }
        return $mapa;
    }

    private static function cargarTareas(PDO $pdo): array
    {
        $rows = $pdo->query(
            'SELECT grupo_id, asignatura_id, sesiones_semana
             FROM distribucion_horaria
             ORDER BY sesiones_semana DESC'
        )->fetchAll();

        $tareas = [];
        foreach ($rows as $row) {
            $grupoId = (int) $row['grupo_id'];
            $asignaturaId = (int) $row['asignatura_id'];
            $sesiones = (int) $row['sesiones_semana'];

            for ($i = 0; $i < $sesiones; $i++) {
                $tareas[] = [
                    'id' => $grupoId . '-' . $asignaturaId . '-' . $i,
                    'grupo_id' => $grupoId,
                    'asignatura_id' => $asignaturaId,
                ];
            }
        }
        return $tareas;
    }

    private static function validarEscenario(array $tareas, array $elegibles): void
    {
        $totalPorGrupo = [];

        foreach ($tareas as $tarea) {
            $clave = $tarea['grupo_id'] . '-' . $tarea['asignatura_id'];
            if (!isset($elegibles[$clave]) || count($elegibles[$clave]) === 0) {
                throw new RuntimeException(
                    sprintf(
                        'No hay docente asignado para el grupo %d y la asignatura %d.',
                        $tarea['grupo_id'],
                        $tarea['asignatura_id']
                    )
                );
            }

            $totalPorGrupo[$tarea['grupo_id']] = ($totalPorGrupo[$tarea['grupo_id']] ?? 0) + 1;
        }

        $sesionesDisponibles = self::SESIONES_DIA - count(self::SESIONES_EXCLUIDAS);
        $maxSlotsGrupo = count(self::DIAS) * $sesionesDisponibles;
        foreach ($totalPorGrupo as $grupoId => $total) {
            if ($total > $maxSlotsGrupo) {
                throw new RuntimeException(
                    sprintf(
                        'El grupo %d necesita %d sesiones, pero solo hay %d huecos semanales.',
                        $grupoId,
                        $total,
                        $maxSlotsGrupo
                    )
                );
            }
        }
    }

    private static function resolver(array $tareasPendientes, array &$estado, array $docentes, array $elegibles): bool
    {
        if (count($tareasPendientes) === 0) {
            return true;
        }

        $estado['nodos']++;
        if ($estado['nodos'] > self::MAX_NODOS_BUSQUEDA) {
            return false;
        }

        $mejorIndice = null;
        $mejoresOpciones = [];

        foreach ($tareasPendientes as $indice => $tarea) {
            $opciones = self::opcionesParaTarea($tarea, $estado, $docentes, $elegibles);
            if (count($opciones) === 0) {
                return false;
            }

            if ($mejorIndice === null || count($opciones) < count($mejoresOpciones)) {
                $mejorIndice = $indice;
                $mejoresOpciones = $opciones;
            }
        }

        usort($mejoresOpciones, static fn(array $a, array $b): int => $a['score'] <=> $b['score']);
        $tarea = $tareasPendientes[$mejorIndice];
        unset($tareasPendientes[$mejorIndice]);
        $tareasPendientes = array_values($tareasPendientes);

        foreach ($mejoresOpciones as $opcion) {
            self::aplicarOpcion($tarea, $opcion, $estado);
            if (self::resolver($tareasPendientes, $estado, $docentes, $elegibles)) {
                return true;
            }
            self::deshacerOpcion($tarea, $opcion, $estado);
        }

        return false;
    }

    private static function opcionesParaTarea(array $tarea, array $estado, array $docentes, array $elegibles): array
    {
        $grupoId = $tarea['grupo_id'];
        $asignaturaId = $tarea['asignatura_id'];
        $claveTarea = $grupoId . '-' . $asignaturaId;
        $opciones = [];

        foreach (self::DIAS as $dia) {
            for ($sesion = 1; $sesion <= self::SESIONES_DIA; $sesion++) {
                if (in_array($sesion, self::SESIONES_EXCLUIDAS, true)) {
                    continue;
                }
                $slot = $dia . '-' . $sesion;
                if (isset($estado['ocupacionGrupo'][$grupoId][$slot])) {
                    continue;
                }

                foreach ($elegibles[$claveTarea] as $docenteId) {
                    if (isset($estado['ocupacionDocente'][$docenteId][$slot])) {
                        continue;
                    }

                    if (($estado['cargaDocente'][$docenteId] ?? 0) >= ($docentes[$docenteId]['horas_maximas'] ?? 0)) {
                        continue;
                    }

                    $opciones[] = [
                        'dia' => $dia,
                        'sesion' => $sesion,
                        'docente_id' => $docenteId,
                        'score' => self::scoreOpcion($grupoId, $asignaturaId, $docenteId, $dia, $sesion, $estado, $docentes),
                    ];
                }
            }
        }

        return $opciones;
    }

    private static function scoreOpcion(
        int $grupoId,
        int $asignaturaId,
        int $docenteId,
        string $dia,
        int $sesion,
        array $estado,
        array $docentes
    ): int {
        $cargaDocente = $estado['cargaDocente'][$docenteId] ?? 0;
        $maxDocente = $docentes[$docenteId]['horas_maximas'] ?: 1;
        $ratioCarga = (int) floor(($cargaDocente * 100) / $maxDocente);

        $cargaGrupoDia = $estado['cargaGrupoDia'][$grupoId][$dia] ?? 0;
        $cargaDocenteDia = $estado['cargaDocenteDia'][$docenteId][$dia] ?? 0;
        $penalizacionHoraTardia = $sesion >= 5 ? 8 : 0;

        $penalizacionConsecutiva = 0;
        $slotAnterior = $dia . '-' . ($sesion - 1);
        if (isset($estado['ocupacionGrupo'][$grupoId][$slotAnterior])) {
            foreach ($estado['asignaciones'] as $asignacion) {
                if (
                    $asignacion['grupo_id'] === $grupoId
                    && $asignacion['dia'] === $dia
                    && $asignacion['sesion'] === ($sesion - 1)
                    && $asignacion['asignatura_id'] === $asignaturaId
                ) {
                    $penalizacionConsecutiva = 15;
                    break;
                }
            }
        }

        return ($ratioCarga * 2) + ($cargaGrupoDia * 4) + ($cargaDocenteDia * 3) + $penalizacionHoraTardia + $penalizacionConsecutiva;
    }

    private static function aplicarOpcion(array $tarea, array $opcion, array &$estado): void
    {
        $grupoId = $tarea['grupo_id'];
        $asignaturaId = $tarea['asignatura_id'];
        $docenteId = $opcion['docente_id'];
        $dia = $opcion['dia'];
        $sesion = $opcion['sesion'];
        $slot = $dia . '-' . $sesion;

        $estado['ocupacionGrupo'][$grupoId][$slot] = true;
        $estado['ocupacionDocente'][$docenteId][$slot] = true;
        $estado['cargaDocente'][$docenteId] = ($estado['cargaDocente'][$docenteId] ?? 0) + 1;
        $estado['cargaGrupoDia'][$grupoId][$dia] = ($estado['cargaGrupoDia'][$grupoId][$dia] ?? 0) + 1;
        $estado['cargaDocenteDia'][$docenteId][$dia] = ($estado['cargaDocenteDia'][$docenteId][$dia] ?? 0) + 1;
        $estado['asignaciones'][] = [
            'grupo_id' => $grupoId,
            'asignatura_id' => $asignaturaId,
            'docente_id' => $docenteId,
            'dia' => $dia,
            'sesion' => $sesion,
        ];
    }

    private static function deshacerOpcion(array $tarea, array $opcion, array &$estado): void
    {
        $grupoId = $tarea['grupo_id'];
        $docenteId = $opcion['docente_id'];
        $dia = $opcion['dia'];
        $sesion = $opcion['sesion'];
        $slot = $dia . '-' . $sesion;

        unset($estado['ocupacionGrupo'][$grupoId][$slot]);
        unset($estado['ocupacionDocente'][$docenteId][$slot]);
        $estado['cargaDocente'][$docenteId]--;
        $estado['cargaGrupoDia'][$grupoId][$dia]--;
        $estado['cargaDocenteDia'][$docenteId][$dia]--;
        array_pop($estado['asignaciones']);
    }

    public static function cargarActual(): array
    {
        $pdo = Database::connection();
        $rows = $pdo->query(
            'SELECT h.dia_semana, h.sesion, h.grupo_id, h.docente_id,
                    g.nombre AS grupo, a.nombre AS asignatura,
                    CONCAT(d.nombre, " ", d.apellido) AS docente
             FROM horarios h
             JOIN grupos g ON g.id = h.grupo_id
             JOIN asignaturas a ON a.id = h.asignatura_id
             JOIN docentes d ON d.id = h.docente_id
             ORDER BY g.nombre, FIELD(h.dia_semana, "Lunes", "Martes", "Miércoles", "Jueves", "Viernes"), h.sesion'
        )->fetchAll();

        if (empty($rows)) {
            return [];
        }

        $agrupado = [];
        foreach ($rows as $fila) {
            $agrupado[$fila['grupo']][$fila['dia_semana']][(int) $fila['sesion']] = $fila;
        }
        return $agrupado;
    }

    public static function cargarPorDocente(): array
    {
        $pdo = Database::connection();
        $rows = $pdo->query(
            'SELECT h.dia_semana, h.sesion, h.grupo_id, h.docente_id,
                    g.nombre AS grupo, a.nombre AS asignatura,
                    CONCAT(d.nombre, " ", d.apellido) AS docente
             FROM horarios h
             JOIN grupos g ON g.id = h.grupo_id
             JOIN asignaturas a ON a.id = h.asignatura_id
             JOIN docentes d ON d.id = h.docente_id
             ORDER BY d.apellido, d.nombre,
                      FIELD(h.dia_semana, "Lunes", "Martes", "Miércoles", "Jueves", "Viernes"), h.sesion'
        )->fetchAll();

        if (empty($rows)) {
            return [];
        }

        $agrupado = [];
        foreach ($rows as $fila) {
            $agrupado[$fila['docente']][$fila['dia_semana']][(int) $fila['sesion']] = $fila;
        }
        return $agrupado;
    }
}
