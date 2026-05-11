<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Grupo.php';
require_once __DIR__ . '/../models/NivelModel.php';
require_once __DIR__ . '/../models/Docente.php';

final class GrupoController extends Controller
{
    public function index(): void
    {
        $this->view('grupos/index', [
            'grupos' => Grupo::all(),
            'titulo' => 'Grupos',
        ]);
    }

    public function create(): void
    {
        $this->view('grupos/form', [
            'grupo' => null,
            'niveles' => NivelModel::all(),
            'docentes' => Docente::all(),
            'action' => 'grupos/store',
            'titulo' => 'Nuevo grupo',
        ]);
    }

    public function store(): void
    {
        $errores = $this->validar($_POST);
        if (!empty($errores)) {
            $this->view('grupos/form', [
                'grupo' => $_POST,
                'niveles' => NivelModel::all(),
                'docentes' => Docente::all(),
                'action' => 'grupos/store',
                'titulo' => 'Nuevo grupo',
                'errores' => $errores,
            ]);
            return;
        }
        $nivelId = (int) ($_POST['nivel_id'] ?? 0);
        $letra = trim((string) ($_POST['letra'] ?? ''));
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        if ($nombre === '') {
            $nombre = self::generarNombre($nivelId, $letra);
        }
        $tutorId = !empty($_POST['tutor_id']) ? (int) $_POST['tutor_id'] : null;
        Grupo::create($nombre, $nivelId, $letra, $tutorId);
        $this->redirect('grupos/index');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $grupo = Grupo::find($id);
        if ($grupo === null) {
            http_response_code(404);
            echo 'Grupo no encontrado';
            return;
        }
        $this->view('grupos/form', [
            'grupo' => $grupo,
            'niveles' => NivelModel::all(),
            'docentes' => Docente::all(),
            'action' => 'grupos/update&id=' . $id,
            'titulo' => 'Editar grupo',
        ]);
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $errores = $this->validar($_POST);
        if (!empty($errores)) {
            $this->view('grupos/form', [
                'grupo' => $_POST,
                'niveles' => NivelModel::all(),
                'docentes' => Docente::all(),
                'action' => 'grupos/update&id=' . $id,
                'titulo' => 'Editar grupo',
                'errores' => $errores,
            ]);
            return;
        }
        $nivelId = (int) ($_POST['nivel_id'] ?? 0);
        $letra = trim((string) ($_POST['letra'] ?? ''));
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        if ($nombre === '') {
            $nombre = self::generarNombre($nivelId, $letra);
        }
        $tutorId = !empty($_POST['tutor_id']) ? (int) $_POST['tutor_id'] : null;
        Grupo::update($id, $nombre, $nivelId, $letra, $tutorId);
        $this->redirect('grupos/index');
    }

    public function destroy(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        Grupo::delete($id);
        $this->redirect('grupos/index');
    }

    private static function generarNombre(int $nivelId, string $letra): string
    {
        $nivel = NivelModel::find($nivelId);
        return ($nivel['nombre'] ?? '') . ' ' . $letra;
    }

    private function validar(array $data): array
    {
        $errores = [];
        if ((int) ($data['nivel_id'] ?? 0) <= 0) $errores['nivel_id'] = 'Selecciona un nivel.';
        if (trim($data['letra'] ?? '') === '') $errores['letra'] = 'Introduce la letra del grupo.';
        if (trim($data['nombre'] ?? '') === '') $errores['nombre'] = 'El nombre del grupo no puede estar vacío.';
        return $errores;
    }
}
