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
            'items' => DocenteAsignacion::allDetailed(),
            'docentes' => Docente::all(),
            'asignaturas' => Asignatura::all(),
            'grupos' => Grupo::all(),
            'titulo' => 'Asignaciones',
        ]);
    }

    public function store(): void
    {
        $errores = [];
        $docenteId = (int) ($_POST['docente_id'] ?? 0);
        $asignaturaId = (int) ($_POST['asignatura_id'] ?? 0);
        $grupoId = (int) ($_POST['grupo_id'] ?? 0);

        if ($docenteId <= 0) $errores['docente_id'] = 'Selecciona un docente.';
        if ($asignaturaId <= 0) $errores['asignatura_id'] = 'Selecciona una asignatura.';
        if ($grupoId <= 0) $errores['grupo_id'] = 'Selecciona un grupo.';

        if (!empty($errores)) {
            $this->view('asignaciones/index', [
                'items' => DocenteAsignacion::allDetailed(),
                'docentes' => Docente::all(),
                'asignaturas' => Asignatura::all(),
                'grupos' => Grupo::all(),
                'titulo' => 'Asignaciones',
                'errores' => $errores,
            ]);
            return;
        }

        DocenteAsignacion::create($docenteId, $asignaturaId, $grupoId);
        $this->redirect('asignaciones/index');
    }

    public function destroy(): void
    {
        DocenteAsignacion::delete((int) ($_GET['id'] ?? 0));
        $this->redirect('asignaciones/index');
    }
}
