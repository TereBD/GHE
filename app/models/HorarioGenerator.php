<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';

final class HorarioGenerator
{
    private const DIAS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
    private const SESIONES_DIA = 6;
    private const SESIONES_EXCLUIDAS = [4];
    private const MAX_NODOS_BUSQUEDA = 100000;
    private const ATU_ASIGNATURA = 'ATU/Religión';
    private const ESPECIALIDADES = ['Educación Física', 'Francés', 'Inglés', 'Música', 'Religión'];
    private const PROYECTO_ASIGNATURA = 'Proyecto';
    private const PROYECTO_GRUPO = 'Proyecto';

    public static function generar(): array
    {
        set_time_limit(0);

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $pdo->exec('DELETE FROM horarios');

            $docentes = self::cargarDocentes($pdo);
            $elegibles = self::cargarElegibles($pdo);
            $tareas = self::cargarTareas($pdo);
            self::validarEscenario($tareas, $elegibles);
            self::validarCargaDocente($tareas, $elegibles, $docentes);

            // Cargar ID de ATU/Religión y mapa de tutores
            $atuId = (int) $pdo->query("SELECT id FROM asignaturas WHERE nombre = '" . self::ATU_ASIGNATURA . "' LIMIT 1")->fetchColumn();
            $tutores = [];
            $rowsT = $pdo->query('SELECT id, tutor_id FROM grupos WHERE tutor_id IS NOT NULL')->fetchAll();
            foreach ($rowsT as $r) {
                $tutores[(int) $r['id']] = (int) $r['tutor_id'];
            }

            // Mapa de nombres de asignaturas para scoring de especialidades
            $nombreAsignaturas = [];
            foreach ($pdo->query('SELECT id, nombre FROM asignaturas')->fetchAll() as $r) {
                $nombreAsignaturas[(int) $r['id']] = $r['nombre'];
            }

            $cargaInicial = [];
            foreach ($docentes as $id => $d) {
                $cargaTutoria = $elegibles['cargaTutoria'][$id] ?? 0;
                // Proyecto se quita de carga inicial: se asignará como sesiones reales después
                $cargaInicial[$id] = $d['horas_pat'] + $cargaTutoria;
            }

            $estado = [
                'ocupacionGrupo' => [],
                'ocupacionDocente' => [],
                'ocupacionTutor' => [],
                'cargaDocente' => $cargaInicial,
                'cargaGrupoDia' => [],
                'cargaDocenteDia' => [],
                'asignaciones' => [],
                'nodos' => 0,
                'atuId' => $atuId,
                'tutores' => $tutores,
                'nombreAsignaturas' => $nombreAsignaturas,
            ];

            // Intentar resolver; si falla, reintentar con orden aleatorio
            $exito = false;
            for ($intento = 0; $intento < 3; $intento++) {
                if ($intento > 0) {
                    shuffle($tareas);
                }
                if (self::resolver($tareas, $estado, $docentes, $elegibles)) {
                    $exito = true;
                    break;
                }
                // Resetear estado entre intentos
                $estado['ocupacionGrupo'] = [];
                $estado['ocupacionDocente'] = [];
                $estado['ocupacionTutor'] = [];
                $estado['cargaDocente'] = $cargaInicial;
                $estado['cargaGrupoDia'] = [];
                $estado['cargaDocenteDia'] = [];
                $estado['asignaciones'] = [];
                $estado['nodos'] = 0;
            }

            if (!$exito) {
                throw new RuntimeException(
                    'No fue posible generar un horario valido con las restricciones actuales. ' .
                    'Revisa asignaciones docente-asignatura-grupo, horas maximas o distribucion horaria.'
                );
            }

            // Asignar sesiones de Proyecto después del horario regular
            self::asignarProyectos($estado, $docentes, $pdo);

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
        $rows = $pdo->query('SELECT id, horas_maximas, horas_pat, horas_proyecto FROM docentes')->fetchAll();
        $docentes = [];
        foreach ($rows as $row) {
            $docentes[(int) $row['id']] = [
                'horas_maximas' => (int) $row['horas_maximas'],
                'horas_pat' => (int) $row['horas_pat'],
                'horas_proyecto' => (int) $row['horas_proyecto'],
            ];
        }
        return $docentes;
    }

    private static function cargarElegibles(PDO $pdo): array
    {
        $rows = $pdo->query('SELECT docente_id, asignatura_id, grupo_id, es_tutoria FROM docente_asig_grupo')->fetchAll();
        $mapa = [];
        $tutorias = [];
        foreach ($rows as $row) {
            $clave = (int) $row['grupo_id'] . '-' . (int) $row['asignatura_id'];
            if ((int) $row['es_tutoria'] === 1) {
                $tutorias[$clave][] = (int) $row['docente_id'];
            } else {
                $mapa[$clave][] = (int) $row['docente_id'];
            }
        }

        // Cargar carga horaria de tutorías
        $cargaTutoria = self::cargarCargaTutoria($pdo, $tutorias);

        return ['regulares' => $mapa, 'tutorias' => $tutorias, 'cargaTutoria' => $cargaTutoria];
    }

    private static function cargarCargaTutoria(PDO $pdo, array $tutorias): array
    {
        if (empty($tutorias)) {
            return [];
        }

        $carga = [];
        $grupos = $pdo->query('SELECT id, nivel_id FROM grupos')->fetchAll();
        $nivelMap = [];
        foreach ($grupos as $g) {
            $nivelMap[(int) $g['id']] = (int) $g['nivel_id'];
        }

        $naRows = $pdo->query('SELECT nivel_id, asignatura_id, sesiones_semana FROM nivel_asignatura')->fetchAll();
        $naMap = [];
        foreach ($naRows as $r) {
            $naMap[(int) $r['nivel_id'] . '-' . (int) $r['asignatura_id']] = (int) $r['sesiones_semana'];
        }

        foreach ($tutorias as $clave => $docentesIds) {
            $parts = explode('-', $clave);
            $grupoId = (int) $parts[0];
            $asignaturaId = (int) $parts[1];
            $nivelId = $nivelMap[$grupoId] ?? 0;
            $sesiones = $naMap[$nivelId . '-' . $asignaturaId] ?? 0;

            foreach ($docentesIds as $docenteId) {
                $carga[$docenteId] = ($carga[$docenteId] ?? 0) + $sesiones;
            }
        }

        return $carga;
    }

    private static function cargarTareas(PDO $pdo): array
    {
        $rows = $pdo->query(
            'SELECT g.id AS grupo_id, na.asignatura_id, na.sesiones_semana
             FROM grupos g
             JOIN nivel_asignatura na ON na.nivel_id = g.nivel_id
             ORDER BY na.sesiones_semana DESC'
        )->fetchAll();

        // Obtener claves que SOLO tienen tutoría (no regular)
        $elegibles = self::cargarElegibles($pdo);
        $soloTutoria = [];
        foreach ($elegibles['tutorias'] as $clave => $docs) {
            if (!isset($elegibles['regulares'][$clave])) {
                $soloTutoria[$clave] = true;
            }
        }

        $tareas = [];
        foreach ($rows as $row) {
            $grupoId = (int) $row['grupo_id'];
            $asignaturaId = (int) $row['asignatura_id'];
            $sesiones = (int) $row['sesiones_semana'];
            $clave = $grupoId . '-' . $asignaturaId;

            // Saltar si solo tiene asignaciones de tutoría
            if (isset($soloTutoria[$clave])) {
                continue;
            }

            for ($i = 0; $i < $sesiones; $i++) {
                $tareas[] = [
                    'id' => $clave . '-' . $i,
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
            if (!isset($elegibles['regulares'][$clave]) || count($elegibles['regulares'][$clave]) === 0) {
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

    private static function validarCargaDocente(array $tareas, array $elegibles, array $docentes): void
    {
        $cargaFija = [];
        foreach ($docentes as $id => $d) {
            $cargaFija[$id] = $d['horas_pat'] + $d['horas_proyecto'];
        }
        foreach ($elegibles['cargaTutoria'] as $id => $horas) {
            $cargaFija[$id] = ($cargaFija[$id] ?? 0) + $horas;
        }

        // Carga mínima que cada docente recibirá por tareas donde es el único elegible
        $cargaExclusiva = [];
        $conteoElegibles = [];
        foreach ($tareas as $tarea) {
            $clave = $tarea['grupo_id'] . '-' . $tarea['asignatura_id'];
            $elegiblesClave = $elegibles['regulares'][$clave] ?? [];
            $conteoElegibles[$clave] = count($elegiblesClave);
            foreach ($elegiblesClave as $docId) {
                $cargaExclusiva[$docId] = ($cargaExclusiva[$docId] ?? 0);
            }
        }
        foreach ($tareas as $tarea) {
            $clave = $tarea['grupo_id'] . '-' . $tarea['asignatura_id'];
            $elegiblesClave = $elegibles['regulares'][$clave] ?? [];
            if ($conteoElegibles[$clave] === 1) {
                $cargaExclusiva[$elegiblesClave[0]]++;
            }
        }

        foreach ($docentes as $id => $d) {
            $totalMinimo = ($cargaFija[$id] ?? 0) + ($cargaExclusiva[$id] ?? 0);
            if ($totalMinimo > $d['horas_maximas']) {
                throw new RuntimeException(
                    sprintf(
                        'El docente ID %d tiene %d horas fijas (PAT+Proyecto+Tutorías+asignaturas exclusivas), pero su máximo es %d.',
                        $id,
                        $totalMinimo,
                        $d['horas_maximas']
                    )
                );
            }
        }

        // Capacidad total vs. tareas totales
        $capTotal = 0;
        foreach ($docentes as $d) {
            $capTotal += $d['horas_maximas'];
        }
        $cargaTotalTareas = count($tareas) + array_sum($cargaFija);
        if ($cargaTotalTareas > $capTotal) {
            throw new RuntimeException(
                sprintf(
                    'La carga total (%d horas) supera la capacidad total del profesorado (%d horas). Revisa las horas máximas asignadas.',
                    $cargaTotalTareas,
                    $capTotal
                )
            );
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
        // Limitar a las 15 mejores opciones para reducir ramificación
        $mejoresOpciones = array_slice($mejoresOpciones, 0, 15);
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

                foreach ($elegibles['regulares'][$claveTarea] as $docenteId) {
                    if (isset($estado['ocupacionDocente'][$docenteId][$slot])) {
                        continue;
                    }
                    if (isset($estado['ocupacionTutor'][$docenteId][$slot])) {
                        continue;
                    }

                    if (($estado['cargaDocente'][$docenteId] ?? 0) >= ($docentes[$docenteId]['horas_maximas'] ?? 0)) {
                        continue;
                    }

                    // Si es ATU/Religión, el tutor del grupo también debe estar libre
                    if ($asignaturaId === $estado['atuId'] && isset($estado['tutores'][$grupoId])) {
                        $tutorId = $estado['tutores'][$grupoId];
                        if (isset($estado['ocupacionDocente'][$tutorId][$slot]) || isset($estado['ocupacionTutor'][$tutorId][$slot])) {
                            continue;
                        }
                        if (($estado['cargaDocente'][$tutorId] ?? 0) >= ($docentes[$tutorId]['horas_maximas'] ?? 0)) {
                            continue;
                        }
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

        // Penalizar especialidades en sesiones tempranas (1-3) para que no siempre
        // le quiten las primeras horas al mismo grupo. Así el grupo puede tener
        // más sesiones con su tutor antes del recreo.
        $penalizacionEspecialidad = 0;
        $nombreAsig = $estado['nombreAsignaturas'][$asignaturaId] ?? '';
        if (in_array($nombreAsig, self::ESPECIALIDADES, true) && $sesion <= 3) {
            $penalizacionEspecialidad = 20;
        }

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

        return ($ratioCarga * 2) + ($cargaGrupoDia * 4) + ($cargaDocenteDia * 3) + $penalizacionHoraTardia + $penalizacionConsecutiva + $penalizacionEspecialidad;
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

        // Bloquear también el slot del tutor si es ATU/Religión
        $tutorBloqueado = null;
        if ($asignaturaId === $estado['atuId'] && isset($estado['tutores'][$grupoId])) {
            $tutorId = $estado['tutores'][$grupoId];
            if (!isset($estado['ocupacionDocente'][$tutorId][$slot]) && !isset($estado['ocupacionTutor'][$tutorId][$slot])) {
                $estado['ocupacionTutor'][$tutorId][$slot] = true;
                $estado['cargaDocente'][$tutorId] = ($estado['cargaDocente'][$tutorId] ?? 0) + 1;
                $estado['cargaDocenteDia'][$tutorId][$dia] = ($estado['cargaDocenteDia'][$tutorId][$dia] ?? 0) + 1;
                $tutorBloqueado = $tutorId;
            }
        }

        $estado['asignaciones'][] = [
            'grupo_id' => $grupoId,
            'asignatura_id' => $asignaturaId,
            'docente_id' => $docenteId,
            'dia' => $dia,
            'sesion' => $sesion,
            'tutor_bloqueado' => $tutorBloqueado,
        ];
    }

    private static function deshacerOpcion(array $tarea, array $opcion, array &$estado): void
    {
        $grupoId = $tarea['grupo_id'];
        $docenteId = $opcion['docente_id'];
        $dia = $opcion['dia'];
        $sesion = $opcion['sesion'];
        $slot = $dia . '-' . $sesion;
        $asignacion = end($estado['asignaciones']);

        unset($estado['ocupacionGrupo'][$grupoId][$slot]);
        unset($estado['ocupacionDocente'][$docenteId][$slot]);
        $estado['cargaDocente'][$docenteId]--;
        $estado['cargaGrupoDia'][$grupoId][$dia]--;
        $estado['cargaDocenteDia'][$docenteId][$dia]--;

        // Desbloquear tutor si aplica
        if ($asignacion && isset($asignacion['tutor_bloqueado']) && $asignacion['tutor_bloqueado'] !== null) {
            $tutorId = $asignacion['tutor_bloqueado'];
            unset($estado['ocupacionTutor'][$tutorId][$slot]);
            $estado['cargaDocente'][$tutorId]--;
            $estado['cargaDocenteDia'][$tutorId][$dia]--;
        }

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

    private static function asignarProyectos(array &$estado, array $docentes, PDO $pdo): void
    {
        // Asegurar que existe la asignatura y el grupo "Proyecto"
        $pdo->exec("INSERT IGNORE INTO asignaturas (nombre) VALUES ('" . self::PROYECTO_ASIGNATURA . "')");
        $proyAsigId = (int) $pdo->query("SELECT id FROM asignaturas WHERE nombre = '" . self::PROYECTO_ASIGNATURA . "' LIMIT 1")->fetchColumn();

        $pdo->exec("INSERT IGNORE INTO grupos (nombre, nivel, letra) VALUES ('" . self::PROYECTO_GRUPO . "', 'PROYECTO', '')");
        $proyGrupoId = (int) $pdo->query("SELECT id FROM grupos WHERE nombre = '" . self::PROYECTO_GRUPO . "' LIMIT 1")->fetchColumn();

        // Proyecto restante por docente
        $proyectoRestante = [];
        foreach ($docentes as $id => $d) {
            $usadas = 0;
            foreach ($estado['asignaciones'] as $a) {
                if ($a['docente_id'] === $id && $a['asignatura_id'] === $proyAsigId) {
                    $usadas++;
                }
            }
            $restante = $d['horas_proyecto'] - $usadas;
            if ($restante > 0) {
                $proyectoRestante[$id] = $restante;
            }
        }

        if (empty($proyectoRestante)) {
            return;
        }

        // Fase 1: cubrir todas las sesiones con al menos un Proyecto
        foreach (self::DIAS as $dia) {
            for ($sesion = 1; $sesion <= self::SESIONES_DIA; $sesion++) {
                if (in_array($sesion, self::SESIONES_EXCLUIDAS, true)) {
                    continue;
                }
                $slot = $dia . '-' . $sesion;

                // Verificar si ya hay algún Proyecto en este slot
                $tieneProyecto = false;
                foreach ($estado['asignaciones'] as $a) {
                    if ($a['dia'] === $dia && $a['sesion'] === $sesion && $a['asignatura_id'] === $proyAsigId) {
                        $tieneProyecto = true;
                        break;
                    }
                }
                if ($tieneProyecto) {
                    continue;
                }

                // Buscar docente con horas de Proyecto restantes y libre en este slot
                foreach ($proyectoRestante as $docId => $restante) {
                    if ($restante <= 0) {
                        continue;
                    }
                    if (isset($estado['ocupacionDocente'][$docId][$slot]) || isset($estado['ocupacionTutor'][$docId][$slot])) {
                        continue;
                    }
                    if (($estado['cargaDocente'][$docId] ?? 0) >= ($docentes[$docId]['horas_maximas'] ?? 0)) {
                        continue;
                    }

                    self::registrarProyecto($estado, $proyGrupoId, $proyAsigId, $docId, $dia, $sesion);
                    $proyectoRestante[$docId]--;
                    break;
                }
            }
        }

        // Fase 2: asignar Proyecto restante en huecos libres (evitar duplicados en grupo Proyecto)
        foreach ($proyectoRestante as $docId => $restante) {
            if ($restante <= 0) {
                continue;
            }
            foreach (self::DIAS as $dia) {
                for ($sesion = 1; $sesion <= self::SESIONES_DIA; $sesion++) {
                    if ($restante <= 0) {
                        break 2;
                    }
                    if (in_array($sesion, self::SESIONES_EXCLUIDAS, true)) {
                        continue;
                    }
                    $slot = $dia . '-' . $sesion;
                    if (isset($estado['ocupacionGrupo'][$proyGrupoId][$slot])) {
                        continue;
                    }
                    if (isset($estado['ocupacionDocente'][$docId][$slot]) || isset($estado['ocupacionTutor'][$docId][$slot])) {
                        continue;
                    }
                    if (($estado['cargaDocente'][$docId] ?? 0) >= ($docentes[$docId]['horas_maximas'] ?? 0)) {
                        continue;
                    }

                    self::registrarProyecto($estado, $proyGrupoId, $proyAsigId, $docId, $dia, $sesion);
                    $restante--;
                }
            }
        }
    }

    private static function registrarProyecto(array &$estado, int $grupoId, int $asignaturaId, int $docenteId, string $dia, int $sesion): void
    {
        $slot = $dia . '-' . $sesion;
        $estado['asignaciones'][] = [
            'grupo_id' => $grupoId,
            'asignatura_id' => $asignaturaId,
            'docente_id' => $docenteId,
            'dia' => $dia,
            'sesion' => $sesion,
            'tutor_bloqueado' => null,
        ];
        $estado['ocupacionGrupo'][$grupoId][$slot] = true;
        $estado['ocupacionDocente'][$docenteId][$slot] = true;
        $estado['cargaDocente'][$docenteId] = ($estado['cargaDocente'][$docenteId] ?? 0) + 1;
        $estado['cargaDocenteDia'][$docenteId][$dia] = ($estado['cargaDocenteDia'][$docenteId][$dia] ?? 0) + 1;
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
