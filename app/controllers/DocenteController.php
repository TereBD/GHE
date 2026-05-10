<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Docente.php';

final class DocenteController extends Controller
{
    public function index(): void
    {
        $this->view('docentes/index', [
            'docentes' => Docente::all(),
            'titulo' => 'Docentes',
        ]);
    }

    public function create(): void
    {
        $this->view('docentes/form', [
            'docente' => null,
            'action' => 'docentes/store',
            'titulo' => 'Nuevo docente',
        ]);
    }

    public function store(): void
    {
        $errores = $this->validar($_POST);
        if (!empty($errores)) {
            $this->view('docentes/form', [
                'docente' => $_POST,
                'action' => 'docentes/store',
                'titulo' => 'Nuevo docente',
                'errores' => $errores,
            ]);
            return;
        }
        Docente::create($this->validatedInput());
        $this->redirect('docentes/index');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $docente = Docente::find($id);

        if ($docente === null) {
            http_response_code(404);
            echo 'Docente no encontrado';
            return;
        }

        $this->view('docentes/form', [
            'docente' => $docente,
            'action' => 'docentes/update&id=' . $id,
            'titulo' => 'Editar docente',
        ]);
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $errores = $this->validar($_POST);
        if (!empty($errores)) {
            $this->view('docentes/form', [
                'docente' => $_POST,
                'action' => 'docentes/update&id=' . $id,
                'titulo' => 'Editar docente',
                'errores' => $errores,
            ]);
            return;
        }
        Docente::update($id, $this->validatedInput());
        $this->redirect('docentes/index');
    }

    public function destroy(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        Docente::delete($id);
        $this->redirect('docentes/index');
    }

    private function validatedInput(): array
    {
        return [
            'nombre' => trim((string) ($_POST['nombre'] ?? '')),
            'apellido' => trim((string) ($_POST['apellido'] ?? '')),
            'tipo' => ($_POST['tipo'] ?? '') === 'especialista' ? 'especialista' : 'tutor',
            'horas_maximas' => (int) ($_POST['horas_maximas'] ?? 0),
            'horas_pat' => (int) ($_POST['horas_pat'] ?? 0),
            'horas_proyecto' => (int) ($_POST['horas_proyecto'] ?? 0),
        ];
    }

    private function validar(array $data): array
    {
        $errores = [];
        if (trim($data['nombre'] ?? '') === '') $errores['nombre'] = 'El nombre es obligatorio.';
        if (trim($data['apellido'] ?? '') === '') $errores['apellido'] = 'El apellido es obligatorio.';
        if ((int) ($data['horas_maximas'] ?? 0) < 1) $errores['horas_maximas'] = 'Las horas máximas deben ser al menos 1.';
        if ((int) ($data['horas_pat'] ?? 0) < 0) $errores['horas_pat'] = 'No puede ser negativo.';
        if ((int) ($data['horas_proyecto'] ?? 0) < 0) $errores['horas_proyecto'] = 'No puede ser negativo.';
        return $errores;
    }
}
