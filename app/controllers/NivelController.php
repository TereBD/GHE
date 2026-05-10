<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/NivelModel.php';
require_once __DIR__ . '/../models/Etapa.php';
require_once __DIR__ . '/../models/Asignatura.php';
require_once __DIR__ . '/../models/NivelAsignatura.php';

final class NivelController extends Controller
{
    public function index(): void
    {
        $this->view('niveles/index', [
            'niveles' => NivelModel::all(),
            'etapas' => Etapa::all(),
            'titulo' => 'Niveles / Cursos',
        ]);
    }

    public function store(): void
    {
        $errores = [];
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $etapaId = (int) ($_POST['etapa_id'] ?? 0);
        $orden = (int) ($_POST['orden'] ?? 0);

        if ($nombre === '') $errores['nombre'] = 'El nombre es obligatorio.';
        if ($etapaId <= 0) $errores['etapa_id'] = 'Selecciona una etapa.';

        if (!empty($errores)) {
            $this->view('niveles/index', [
                'niveles' => NivelModel::all(),
                'etapas' => Etapa::all(),
                'titulo' => 'Niveles / Cursos',
                'errores' => $errores,
            ]);
            return;
        }

        try {
            NivelModel::create($nombre, $etapaId, $orden);
        } catch (Throwable $e) {
            // Duplicado
        }

        $this->redirect('niveles/index');
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $etapaId = (int) ($_POST['etapa_id'] ?? 0);
        $orden = (int) ($_POST['orden'] ?? 0);

        if ($nombre !== '') {
            NivelModel::update($id, $nombre, $etapaId, $orden);
        }

        $this->redirect('niveles/index');
    }

    public function destroy(): void
    {
        NivelModel::delete((int) ($_GET['id'] ?? 0));
        $this->redirect('niveles/index');
    }

    public function sesiones(): void
    {
        $data = NivelAsignatura::matrixData();

        $this->view('niveles/sesiones', [
            'niveles' => $data['niveles'],
            'asignaturas' => $data['asignaturas'],
            'map' => $data['map'],
            'titulo' => 'Sesiones por nivel',
        ]);
    }

    public function guardarSesiones(): void
    {
        $datos = $_POST['sesiones'] ?? [];

        foreach ($datos as $nivelId => $asignaturas) {
            foreach ($asignaturas as $asignaturaId => $sesiones) {
                $sesiones = (int) $sesiones;
                if ($sesiones >= 0 && $sesiones <= 30) {
                    if ($sesiones > 0) {
                        NivelAsignatura::upsert((int) $nivelId, (int) $asignaturaId, $sesiones);
                    } else {
                        // Si es 0, eliminar el registro si existe
                        $pdo = Database::connection();
                        $stmt = $pdo->prepare('DELETE FROM nivel_asignatura WHERE nivel_id = ? AND asignatura_id = ?');
                        $stmt->execute([(int) $nivelId, (int) $asignaturaId]);
                    }
                }
            }
        }

        $this->redirect('niveles/sesiones');
    }
}
