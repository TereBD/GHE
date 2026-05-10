<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/DocenteAsignacion.php';
require_once __DIR__ . '/../models/Docente.php';
require_once __DIR__ . '/../models/Asignatura.php';
require_once __DIR__ . '/../models/Grupo.php';

final class AsignacionController extends Controller
{
    public function index(): void
    {
        $this->view('asignaciones/index', [
            'items' => DocenteAsignacion::allGroupedByDocente(),
            'docentes' => Docente::all(),
            'asignaturas' => Asignatura::all(),
            'grupos' => Grupo::reales(),
            'titulo' => 'Asignaciones',
        ]);
    }

    public function store(): void
    {
        $errores = [];
        $docenteId = (int) ($_POST['docente_id'] ?? 0);
        $modo = $_POST['modo'] ?? 'tutor';

        if ($docenteId <= 0) {
            $errores['docente_id'] = 'Selecciona un docente.';
        }

        $docente = Docente::find($docenteId);
        $tipo = $docente['tipo'] ?? 'tutor';

        if ($tipo === 'especialista') {
            // Modo especialista: seleccionar asignatura + grupos
            $asignaturaId = (int) ($_POST['asignatura_id'] ?? 0);
            $gruposIds = $_POST['grupos_ids'] ?? [];

            if ($asignaturaId <= 0) $errores['asignatura_id'] = 'Selecciona una asignatura.';
            if (!is_array($gruposIds) || count($gruposIds) === 0) {
                $errores['grupos_ids'] = 'Selecciona al menos un grupo.';
            }

            if (!empty($errores)) {
                $this->view('asignaciones/index', [
                    'items' => DocenteAsignacion::allGroupedByDocente(),
                    'docentes' => Docente::all(),
                    'asignaturas' => Asignatura::all(),
                    'grupos' => Grupo::reales(),
                    'titulo' => 'Asignaciones',
                    'errores' => $errores,
                ]);
                return;
            }

            foreach ($gruposIds as $grupoId) {
                try {
                    DocenteAsignacion::create($docenteId, $asignaturaId, (int) $grupoId, false);
                } catch (Throwable $e) {
                    // Ignorar duplicados
                }
            }
        } else {
            // Modo tutor: seleccionar grupo + asignaturas
            $grupoId = (int) ($_POST['grupo_id'] ?? 0);
            $asignaturasIds = $_POST['asignaturas_ids'] ?? [];
            $tutoriasIds = $_POST['tutorias_ids'] ?? [];

            if ($grupoId <= 0) $errores['grupo_id'] = 'Selecciona un grupo.';
            if (!is_array($asignaturasIds) || count($asignaturasIds) === 0) {
                $errores['asignaturas_ids'] = 'Selecciona al menos una asignatura.';
            }

            if (!empty($errores)) {
                $this->view('asignaciones/index', [
                    'items' => DocenteAsignacion::allGroupedByDocente(),
                    'docentes' => Docente::all(),
                    'asignaturas' => Asignatura::all(),
                    'grupos' => Grupo::reales(),
                    'titulo' => 'Asignaciones',
                    'errores' => $errores,
                ]);
                return;
            }

            foreach ($asignaturasIds as $asignaturaId) {
                $esTutoria = is_array($tutoriasIds) && in_array($asignaturaId, $tutoriasIds);
                try {
                    DocenteAsignacion::create($docenteId, (int) $asignaturaId, $grupoId, $esTutoria);
                } catch (Throwable $e) {
                    // Ignorar duplicados
                }
            }
        }

        $this->redirect('asignaciones/index');
    }

    public function destroy(): void
    {
        DocenteAsignacion::delete((int) ($_GET['id'] ?? 0));
        $this->redirect('asignaciones/index');
    }
}
