<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/DistribucionHoraria.php';
require_once __DIR__ . '/../models/Grupo.php';
require_once __DIR__ . '/../models/Asignatura.php';

final class DistribucionController extends Controller
{
    public function index(): void
    {
        $this->view('distribucion/index', [
            'items' => DistribucionHoraria::allDetailed(),
            'grupos' => Grupo::all(),
            'asignaturas' => Asignatura::all(),
            'titulo' => 'Distribución horaria',
        ]);
    }

    public function store(): void
    {
        $errores = [];
        $grupoId = (int) ($_POST['grupo_id'] ?? 0);
        $asignaturaId = (int) ($_POST['asignatura_id'] ?? 0);
        $sesiones = (int) ($_POST['sesiones_semana'] ?? 0);

        if ($grupoId <= 0) $errores['grupo_id'] = 'Selecciona un grupo.';
        if ($asignaturaId <= 0) $errores['asignatura_id'] = 'Selecciona una asignatura.';
        if ($sesiones < 1 || $sesiones > 30) $errores['sesiones_semana'] = 'Las sesiones deben ser entre 1 y 30.';

        if (!empty($errores)) {
            $this->view('distribucion/index', [
                'items' => DistribucionHoraria::allDetailed(),
                'grupos' => Grupo::all(),
                'asignaturas' => Asignatura::all(),
                'titulo' => 'Distribución horaria',
                'errores' => $errores,
            ]);
            return;
        }

        DistribucionHoraria::create($grupoId, $asignaturaId, $sesiones);
        $this->redirect('distribucion/index');
    }

    public function destroy(): void
    {
        DistribucionHoraria::delete((int) ($_GET['id'] ?? 0));
        $this->redirect('distribucion/index');
    }
}
