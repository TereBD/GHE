<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Asignatura.php';

final class AsignaturaController extends Controller
{
    public function index(): void
    {
        $this->view('asignaturas/index', [
            'asignaturas' => Asignatura::all(),
            'titulo' => 'Asignaturas',
        ]);
    }

    public function create(): void
    {
        $this->view('asignaturas/form', [
            'asignatura' => null,
            'action' => 'asignaturas/store',
            'titulo' => 'Nueva asignatura',
        ]);
    }

    public function store(): void
    {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        if ($nombre === '') {
            $this->view('asignaturas/form', [
                'asignatura' => null,
                'action' => 'asignaturas/store',
                'titulo' => 'Nueva asignatura',
                'errores' => ['nombre' => 'El nombre es obligatorio.'],
            ]);
            return;
        }
        Asignatura::create($nombre);
        $this->redirect('asignaturas/index');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $asignatura = Asignatura::find($id);
        if ($asignatura === null) {
            http_response_code(404);
            echo 'Asignatura no encontrada';
            return;
        }
        $this->view('asignaturas/form', [
            'asignatura' => $asignatura,
            'action' => 'asignaturas/update&id=' . $id,
            'titulo' => 'Editar asignatura',
        ]);
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        if ($nombre === '') {
            $this->view('asignaturas/form', [
                'asignatura' => null,
                'action' => 'asignaturas/update&id=' . $id,
                'titulo' => 'Editar asignatura',
                'errores' => ['nombre' => 'El nombre es obligatorio.'],
            ]);
            return;
        }
        Asignatura::update($id, $nombre);
        $this->redirect('asignaturas/index');
    }

    public function destroy(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        Asignatura::delete($id);
        $this->redirect('asignaturas/index');
    }
}
