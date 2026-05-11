<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Grupo.php';

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
            'action' => 'grupos/store',
            'titulo' => 'Nuevo grupo',
        ]);
    }

    public function store(): void
    {
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        if ($nombre === '') {
            $this->view('grupos/form', [
                'grupo' => null,
                'action' => 'grupos/store',
                'titulo' => 'Nuevo grupo',
                'errores' => ['nombre' => 'El nombre es obligatorio.'],
            ]);
            return;
        }
        Grupo::create($nombre);
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
            'action' => 'grupos/update&id=' . $id,
            'titulo' => 'Editar grupo',
        ]);
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        if ($nombre === '') {
            $this->view('grupos/form', [
                'grupo' => null,
                'action' => 'grupos/update&id=' . $id,
                'titulo' => 'Editar grupo',
                'errores' => ['nombre' => 'El nombre es obligatorio.'],
            ]);
            return;
        }
        Grupo::update($id, $nombre);
        $this->redirect('grupos/index');
    }

    public function destroy(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        Grupo::delete($id);
        $this->redirect('grupos/index');
    }
}
