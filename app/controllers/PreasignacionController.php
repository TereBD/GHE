<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Preasignacion.php';
require_once __DIR__ . '/../models/Docente.php';
require_once __DIR__ . '/../models/Grupo.php';

final class PreasignacionController extends Controller
{
    public function index(): void
    {
        $preasignaciones = Preasignacion::all();
        $docentes = Docente::all();
        $grupos = Grupo::reales();

        $this->view('preasignaciones/index', [
            'preasignaciones' => $preasignaciones,
            'docentes' => $docentes,
            'grupos' => $grupos,
            'titulo' => 'Pre-asignaciones',
        ]);
    }

    public function asignar(): void
    {
        $docenteId = (int) ($_POST['docente_id'] ?? 0);
        $grupoId = (int) ($_POST['grupo_id'] ?? 0);
        $asignaturaId = (int) ($_POST['asignatura_id'] ?? 0);
        $dia = trim((string) ($_POST['dia'] ?? ''));
        $sesion = (int) ($_POST['sesion'] ?? 0);

        if ($docenteId > 0 && $grupoId > 0 && $asignaturaId > 0 && $dia !== '' && $sesion >= 1 && $sesion <= 6 && $sesion !== 4) {
            try {
                Preasignacion::asignar($docenteId, $grupoId, $asignaturaId, $dia, $sesion);
            } catch (Throwable $e) {
                // Conflicto de unique key (docente o grupo ya ocupado en ese slot)
            }
        }

        $this->redirect('preasignaciones/index');
    }

    public function eliminar(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            Preasignacion::eliminar($id);
        }
        $this->redirect('preasignaciones/index');
    }

    public function asignaturas(): void
    {
        $docenteId = (int) ($_GET['docente_id'] ?? 0);
        $grupoId = (int) ($_GET['grupo_id'] ?? 0);

        header('Content-Type: application/json');
        if ($docenteId > 0 && $grupoId > 0) {
            echo json_encode(Preasignacion::asignaturasDisponibles($docenteId, $grupoId));
        } else {
            echo json_encode([]);
        }
        exit;
    }
}
